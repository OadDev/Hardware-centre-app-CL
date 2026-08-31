<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
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

    public function store(Request $request, Order $order)
    {
        $isNew = ! $order->invoice;
        $invoice = Invoice::generateForOrder($order, $request->user()?->id);

        return response()->json($this->transform($invoice), $isNew ? 201 : 200);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['order.items.product', 'order.items.productVariant', 'customer']);

        return response()->json([
            ...$this->transform($invoice),
            'lineItems' => $invoice->lineItems(),
        ]);
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['order.items.product.hsnCode', 'order.items.productVariant', 'customer']);

        $pdf = Pdf::loadView('admin.invoice-pdf', [
            'invoice' => $invoice,
            'order' => $invoice->order,
            'lineItems' => $invoice->lineItems(),
        ])->setPaper('a4');

        $filename = str_replace(['/', '\\'], '-', $invoice->invoice_number).'.pdf';

        return $pdf->stream($filename);
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
