<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::query()->with(['order', 'customer']);

        if ($search = $request->query('q')) {
            $query->where(function ($w) use ($search) {
                $w->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('billing_name', 'like', "%{$search}%")
                    ->orWhereHas('order', fn ($o) => $o->where('order_no', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->orderByDesc('issued_at')->paginate($request->integer('per_page', 20));
        $invoices->getCollection()->transform(fn (Invoice $i) => $this->transform($i));

        return response()->json($invoices);
    }

    /**
     * Generates an invoice for the order, or simply returns the one that
     * already exists — invoice numbers are sequential and must never be
     * reused or skipped, so re-generating on every click would be wrong.
     */
    public function store(Request $request, Order $order)
    {
        if ($order->invoice) {
            return response()->json($this->transform($order->invoice), 200);
        }

        $order->loadMissing(['customer', 'address']);
        $settings = AppSetting::current();
        $address = $order->address;

        $invoice = Invoice::create([
            'invoice_number' => Invoice::nextNumber(),
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'created_by_user_id' => $request->user()?->id,

            'business_name' => $settings->business_name,
            'business_gst_number' => $settings->gst_number,
            'business_address' => $settings->registered_address,

            'billing_name' => $address?->contact_name ?: $order->customer->name,
            'billing_gst_number' => $order->gst_number ?: $address?->gst_number,
            'billing_address' => $address ? "{$address->line}, {$address->city}, {$address->state} - {$address->pincode}" : null,
            'billing_email' => $order->customer_email ?: $order->customer->email,
            'billing_phone' => $order->customer->phone,

            'subtotal' => $order->subtotal,
            'discount' => $order->discount,
            'gst_amount' => $order->gst_amount,
            'delivery_charge' => $order->delivery_charge,
            'total' => $order->total,

            'issued_at' => now(),
        ]);

        return response()->json($this->transform($invoice), 201);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['order.items.product', 'order.items.productVariant', 'customer']);

        return response()->json([
            ...$this->transform($invoice),
            'lineItems' => $this->lineItems($invoice),
        ]);
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['order.items.product.hsnCode', 'order.items.productVariant', 'customer']);

        $pdf = Pdf::loadView('admin.invoice-pdf', [
            'invoice' => $invoice,
            'order' => $invoice->order,
            'lineItems' => $this->lineItems($invoice),
        ])->setPaper('a4');

        $filename = str_replace(['/', '\\'], '-', $invoice->invoice_number).'.pdf';

        return $pdf->stream($filename);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function lineItems(Invoice $invoice): array
    {
        $order = $invoice->order;
        $taxableBase = (float) $order->subtotal - (float) $order->discount;

        return $order->items->map(function (OrderItem $item) use ($order, $taxableBase) {
            $lineTotal = (float) $item->line_total;
            $lineShare = (float) $order->subtotal > 0 ? $lineTotal / (float) $order->subtotal : 0;
            $lineTaxable = round($lineShare * $taxableBase, 2);
            $gstRate = $item->product?->effectiveGstRate() ?? 0;
            $lineGst = round($lineTaxable * ($gstRate / 100), 2);

            return [
                'product' => $item->product->name ?? 'Product removed',
                'variant' => $item->variant_label,
                'hsnCode' => $item->product?->hsnCode?->code,
                'quantity' => $item->quantity,
                'unitPrice' => (float) $item->unit_price,
                'taxableValue' => $lineTaxable,
                'gstRate' => (float) $gstRate,
                'gstAmount' => $lineGst,
                'lineTotal' => round($lineTaxable + $lineGst, 2),
            ];
        })->all();
    }

    private function transform(Invoice $i): array
    {
        $i->loadMissing(['order', 'customer']);

        return [
            'id' => $i->id,
            'invoiceNumber' => $i->invoice_number,
            'orderNo' => $i->order->order_no,
            'customerName' => $i->billing_name,
            'issuedAt' => $i->issued_at->toIso8601String(),
            'subtotal' => (float) $i->subtotal,
            'discount' => (float) $i->discount,
            'gstAmount' => (float) $i->gst_amount,
            'deliveryCharge' => (float) $i->delivery_charge,
            'total' => (float) $i->total,
        ];
    }
}
