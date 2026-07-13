<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'table_number' => 'nullable|string|max:50',
            'event_date' => 'nullable|date',
            'service_type' => 'required|string|in:dine_in,catering,nasi_box,Semua',
            'payment_method' => 'required|string|in:tunai,qris,transfer',
            'payment_status' => 'nullable|string|in:lunas,dp',
            'total_amount' => 'required|numeric|min:0',
            'cash_received' => 'nullable|numeric|min:0',
            'dp_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.nama' => 'required|string',
            'items.*.harga' => 'required|numeric',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $paymentStatus = $request->payment_status ?: 'lunas';
            $dpAmount = $paymentStatus === 'dp' ? $request->dp_amount : null;
            $remainingAmount = $paymentStatus === 'dp' ? ($request->total_amount - $dpAmount) : 0;
            
            // Validate DP logic
            if ($paymentStatus === 'dp' && $dpAmount >= $request->total_amount) {
                 throw new \Exception('Nominal DP tidak boleh lebih besar atau sama dengan total belanja.');
            }

            $changeAmount = null;
            if ($request->payment_method === 'tunai' && $request->cash_received) {
                // If DP, change is based on dp_amount, otherwise total_amount
                $targetAmount = $paymentStatus === 'dp' ? $dpAmount : $request->total_amount;
                $changeAmount = $request->cash_received - $targetAmount;
                if ($changeAmount < 0) {
                    throw new \Exception('Uang tunai kurang dari nominal yang harus dibayar.');
                }
            }

            // Generate Invoice Number
            $prefix = 'INV/' . date('Ymd') . '/';
            $lastOrder = Order::where('invoice_number', 'LIKE', $prefix . '%')->orderBy('id', 'desc')->first();
            $nextNumber = 1;
            if ($lastOrder) {
                $lastNumber = (int) substr($lastOrder->invoice_number, -4);
                $nextNumber = $lastNumber + 1;
            }
            $invoiceNumber = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            
            $serviceType = $request->service_type === 'Semua' ? 'dine_in' : $request->service_type;

            $order = Order::create([
                'invoice_number' => $invoiceNumber,
                'customer_name' => $request->customer_name,
                'table_number' => $request->table_number,
                'event_date' => $request->event_date,
                'service_type' => $serviceType,
                'total_amount' => $request->total_amount,
                'payment_status' => $paymentStatus,
                'dp_amount' => $dpAmount,
                'remaining_amount' => $remainingAmount,
                'payment_method' => $request->payment_method,
                'cash_received' => $request->cash_received,
                'change_amount' => $changeAmount,
                'note' => $request->note,
                'user_id' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['nama'],
                    'price' => $item['harga'],
                    'quantity' => $item['qty'],
                    'subtotal' => $item['harga'] * $item['qty']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function receipt($id)
    {
        $order = Order::with('items')->findOrFail($id);
        return view('pos.receipt', compact('order'));
    }
}
