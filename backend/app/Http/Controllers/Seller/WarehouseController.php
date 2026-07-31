<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\WarehouseShipment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $seller = $request->user()->seller;

        $inventory = $seller->warehouseInventories()
            ->with(['warehouse', 'product', 'productVariant'])
            ->orderByDesc('updated_at')
            ->paginate(15, ['*'], 'inventory_page');

        $shipments = $seller->warehouseShipments()
            ->with(['warehouse', 'items.product', 'items.productVariant'])
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'shipments_page');

        return view('seller.warehouse', [
            'seller' => $seller,
            'inventory' => $inventory,
            'shipments' => $shipments,
            'products' => $seller->products()->with('variants')->orderBy('name')->get(),
        ]);
    }

    public function storeShipment(Request $request)
    {
        $seller = $request->user()->seller;

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
            'expected_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_code' => ['required', 'string', Rule::exists('products', 'product_code')->where('seller_id', $seller->id)],
            'items.*.variant_code' => ['nullable', 'string', 'exists:product_variants,variant_code'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $warehouse = Warehouse::where('status', 'Active')->orderBy('id')->firstOrFail();

        $nextSeq = 1 + WarehouseShipment::count();
        do {
            $shipmentNo = 'WHS-'.now()->format('Y').'-'.str_pad((string) $nextSeq, 5, '0', STR_PAD_LEFT);
            $nextSeq++;
        } while (WarehouseShipment::where('shipment_no', $shipmentNo)->exists());

        $shipment = WarehouseShipment::create([
            'shipment_no' => $shipmentNo,
            'warehouse_id' => $warehouse->id,
            'seller_id' => $seller->id,
            'status' => 'Pending',
            'notes' => $data['notes'] ?? null,
            'expected_at' => $data['expected_at'] ?? null,
        ]);

        foreach ($data['items'] as $row) {
            $product = $seller->products()->where('product_code', $row['product_code'])->firstOrFail();
            $variant = ! empty($row['variant_code'])
                ? $product->variants()->where('variant_code', $row['variant_code'])->first()
                : null;

            $shipment->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity_expected' => $row['quantity'],
            ]);
        }

        return back()->with('status', 'Shipment '.$shipment->shipment_no.' created — send your stock to the warehouse and we\'ll confirm once received.');
    }
}
