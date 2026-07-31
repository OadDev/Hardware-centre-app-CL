<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Warehouse;
use App\Models\WarehouseInventory;
use App\Models\WarehouseShipment;
use App\Models\WarehouseShipmentItem;
use App\Models\WarehouseStockMovement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 10;

    public function index()
    {
        $warehouses = Warehouse::orderBy('name')->get()->map(fn (Warehouse $w) => [
            'id' => $w->code,
            'name' => $w->name,
            'address' => $w->address,
            'city' => $w->city,
            'state' => $w->state,
            'phone' => $w->phone,
            'status' => $w->status,
            'skuCount' => WarehouseInventory::where('warehouse_id', $w->id)->count(),
            'lowStockCount' => WarehouseInventory::where('warehouse_id', $w->id)
                ->where('quantity_available', '<=', self::LOW_STOCK_THRESHOLD)->count(),
            'pendingShipments' => WarehouseShipment::where('warehouse_id', $w->id)
                ->whereIn('status', ['Pending', 'In Transit'])->count(),
        ]);

        return response()->json([
            'warehouses' => $warehouses,
            'dispatch_pending' => OrderItem::where('fulfillment_type', 'warehouse')
                ->whereIn('dispatch_status', ['Pending', 'Packed'])->count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
        ]);

        $nextCode = 1 + Warehouse::count();
        while (Warehouse::where('code', 'WH-'.str_pad((string) $nextCode, 3, '0', STR_PAD_LEFT))->exists()) {
            $nextCode++;
        }

        $warehouse = Warehouse::create([
            ...$data,
            'code' => 'WH-'.str_pad((string) $nextCode, 3, '0', STR_PAD_LEFT),
            'status' => $data['status'] ?? 'Active',
        ]);

        return response()->json(['id' => $warehouse->code, 'message' => 'Warehouse created'], 201);
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:120'],
            'state' => ['sometimes', 'nullable', 'string', 'max:120'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
        ]);

        $warehouse->update($data);

        return response()->json(['message' => 'Warehouse updated']);
    }

    public function inventory(Request $request)
    {
        $query = WarehouseInventory::with(['warehouse', 'seller', 'product', 'productVariant']);

        if ($sellerCode = $request->query('seller')) {
            $query->whereHas('seller', fn ($q) => $q->where('seller_code', $sellerCode));
        }
        if ($request->boolean('low_stock')) {
            $query->where('quantity_available', '<=', self::LOW_STOCK_THRESHOLD);
        }
        if ($search = $request->query('search')) {
            $query->whereHas('product', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $rows = $query->orderByDesc('updated_at')->paginate($request->integer('per_page', 20));
        $rows->getCollection()->transform(fn (WarehouseInventory $i) => $this->transformInventory($i));

        return response()->json($rows);
    }

    public function adjustInventory(Request $request, WarehouseInventory $inventory)
    {
        $data = $request->validate([
            'quantity_delta' => ['required', 'integer'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $newQuantity = $inventory->quantity_available + $data['quantity_delta'];
        abort_if($newQuantity < 0, 422, 'Adjustment would take available stock below zero.');

        $inventory->update(['quantity_available' => $newQuantity]);

        WarehouseStockMovement::create([
            'warehouse_id' => $inventory->warehouse_id,
            'seller_id' => $inventory->seller_id,
            'product_id' => $inventory->product_id,
            'product_variant_id' => $inventory->product_variant_id,
            'type' => 'adjustment',
            'quantity_delta' => $data['quantity_delta'],
            'note' => $data['note'] ?? null,
            'created_by_user_id' => $request->user()->id,
        ]);

        return response()->json($this->transformInventory($inventory->fresh(['warehouse', 'seller', 'product', 'productVariant'])));
    }

    public function shipments(Request $request)
    {
        $query = WarehouseShipment::with(['warehouse', 'seller', 'items.product', 'items.productVariant']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $shipments = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 20));
        $shipments->getCollection()->transform(fn (WarehouseShipment $s) => $this->transformShipment($s));

        return response()->json($shipments);
    }

    public function showShipment(WarehouseShipment $shipment)
    {
        $shipment->load(['warehouse', 'seller', 'items.product', 'items.productVariant']);

        return response()->json($this->transformShipment($shipment));
    }

    public function receiveShipment(Request $request, WarehouseShipment $shipment)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', 'exists:warehouse_shipment_items,id'],
            'items.*.quantity_received' => ['required', 'integer', 'min:0'],
        ]);

        $shipment->load('items');

        foreach ($data['items'] as $row) {
            /** @var WarehouseShipmentItem|null $item */
            $item = $shipment->items->firstWhere('id', $row['item_id']);
            if (! $item) {
                continue;
            }

            $delta = $row['quantity_received'] - $item->quantity_received;
            if ($delta === 0) {
                continue;
            }

            $item->update(['quantity_received' => $row['quantity_received']]);

            $inventory = WarehouseInventory::firstOrCreate([
                'warehouse_id' => $shipment->warehouse_id,
                'seller_id' => $shipment->seller_id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
            ]);
            $inventory->increment('quantity_available', $delta);

            WarehouseStockMovement::create([
                'warehouse_id' => $shipment->warehouse_id,
                'seller_id' => $shipment->seller_id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'type' => 'inbound_received',
                'quantity_delta' => $delta,
                'reference_type' => 'warehouse_shipment',
                'reference_id' => $shipment->id,
                'note' => 'Received against shipment '.$shipment->shipment_no,
                'created_by_user_id' => $request->user()->id,
            ]);
        }

        $shipment->load('items');
        $totalExpected = $shipment->items->sum('quantity_expected');
        $totalReceived = $shipment->items->sum('quantity_received');

        $shipment->update([
            'status' => $totalReceived >= $totalExpected ? 'Received' : ($totalReceived > 0 ? 'Partially Received' : $shipment->status),
            'received_at' => $shipment->received_at ?? ($totalReceived > 0 ? now() : null),
        ]);

        return response()->json($this->transformShipment($shipment->fresh(['warehouse', 'seller', 'items.product', 'items.productVariant'])));
    }

    public function dispatchQueue(Request $request)
    {
        $query = OrderItem::where('fulfillment_type', 'warehouse')
            ->with(['order.customer', 'product', 'productVariant', 'seller', 'warehouse']);

        if ($status = $request->query('status')) {
            $query->where('dispatch_status', $status);
        } else {
            $query->whereIn('dispatch_status', ['Pending', 'Packed']);
        }

        $rows = $query->orderBy('created_at')->paginate($request->integer('per_page', 20));
        $rows->getCollection()->transform(fn (OrderItem $i) => $this->transformDispatchItem($i));

        return response()->json($rows);
    }

    public function packItem(OrderItem $item)
    {
        abort_unless($item->fulfillment_type === 'warehouse' && $item->dispatch_status === 'Pending', 422, 'Item is not awaiting packing.');

        $item->update(['dispatch_status' => 'Packed']);

        return response()->json($this->transformDispatchItem($item->fresh(['order.customer', 'product', 'productVariant', 'seller', 'warehouse'])));
    }

    public function dispatchItem(Request $request, OrderItem $item)
    {
        abort_unless($item->fulfillment_type === 'warehouse' && in_array($item->dispatch_status, ['Pending', 'Packed'], true), 422, 'Item is not ready to dispatch.');

        $inventory = WarehouseInventory::where('warehouse_id', $item->warehouse_id)
            ->where('seller_id', $item->seller_id)
            ->where('product_id', $item->product_id)
            ->where('product_variant_id', $item->product_variant_id)
            ->first();

        if ($inventory) {
            $inventory->decrement('quantity_reserved', min($item->quantity, $inventory->quantity_reserved));
        }

        WarehouseStockMovement::create([
            'warehouse_id' => $item->warehouse_id,
            'seller_id' => $item->seller_id,
            'product_id' => $item->product_id,
            'product_variant_id' => $item->product_variant_id,
            'type' => 'order_dispatched',
            'quantity_delta' => -$item->quantity,
            'reference_type' => 'order_item',
            'reference_id' => $item->id,
            'note' => 'Dispatched for order '.$item->order->order_no,
            'created_by_user_id' => $request->user()->id,
        ]);

        $item->update(['dispatch_status' => 'Dispatched']);

        return response()->json($this->transformDispatchItem($item->fresh(['order.customer', 'product', 'productVariant', 'seller', 'warehouse'])));
    }

    private function transformInventory(WarehouseInventory $i): array
    {
        return [
            'id' => $i->id,
            'warehouse' => $i->warehouse->name,
            'warehouseCode' => $i->warehouse->code,
            'seller' => $i->seller->store_name,
            'sellerCode' => $i->seller->seller_code,
            'product' => $i->product->name,
            'variant' => $i->productVariant?->label,
            'quantityAvailable' => $i->quantity_available,
            'quantityReserved' => $i->quantity_reserved,
            'lowStock' => $i->quantity_available <= self::LOW_STOCK_THRESHOLD,
            'updatedAt' => $i->updated_at->toIso8601String(),
        ];
    }

    private function transformShipment(WarehouseShipment $s): array
    {
        return [
            'id' => $s->shipment_no,
            'warehouse' => $s->warehouse->name,
            'seller' => $s->seller->store_name,
            'sellerCode' => $s->seller->seller_code,
            'status' => $s->status,
            'notes' => $s->notes,
            'expectedAt' => $s->expected_at?->toDateString(),
            'receivedAt' => $s->received_at?->toIso8601String(),
            'createdAt' => $s->created_at->toIso8601String(),
            'items' => $s->items->map(fn ($item) => [
                'id' => $item->id,
                'product' => $item->product->name,
                'variant' => $item->productVariant?->label,
                'quantityExpected' => $item->quantity_expected,
                'quantityReceived' => $item->quantity_received,
            ]),
        ];
    }

    private function transformDispatchItem(OrderItem $i): array
    {
        return [
            'id' => $i->id,
            'orderId' => $i->order->order_no,
            'customerName' => $i->order->customer->name,
            'product' => $i->product->name,
            'variant' => $i->productVariant?->label,
            'quantity' => $i->quantity,
            'seller' => $i->seller->store_name,
            'warehouse' => $i->warehouse?->name,
            'dispatchStatus' => $i->dispatch_status,
            'placedAt' => $i->order->placed_at->toIso8601String(),
        ];
    }
}
