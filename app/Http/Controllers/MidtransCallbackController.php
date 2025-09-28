<?php
namespace App\Http\Controllers;

use App\Models\Orders; // Pastikan nama model Anda Order, bukan Orders
use Midtrans\Config;
use Midtrans\Notification;
use Illuminate\Http\Request;

class MidtransCallbackController extends Controller
{
    public function handle(Request $request)
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION');

        try {
            // Buat objek notifikasi, ini sudah otomatis memverifikasi signature
            $notif = new Notification();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid notification signature'], 400);
        }

        $orderId = $notif->order_id;
        $transactionStatus = $notif->transaction_status;
        $fraudStatus = $notif->fraud_status;

        // Cari pesanan di database berdasarkan nomor invoice
        $order = Orders::where('invoice_number', $orderId)->first();

        // 1. Tangani jika pesanan tidak ditemukan
        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // 2. Tangani jika pesanan sudah dibayar sebelumnya (mencegah duplikasi proses)
        if ($order->status == 'paid') {
            return response()->json(['message' => 'Order already paid.']);
        }

        // 3. Tangani notifikasi pembayaran sukses
        if (($transactionStatus == 'capture' || $transactionStatus == 'settlement') && $fraudStatus == 'accept') {
            // Update status pesanan di database Anda menjadi 'paid'
            $order->update(['status' => 'paid']);
        }
        // 4. Tangani notifikasi pembayaran gagal, dibatalkan, atau kedaluwarsa
        else if ($transactionStatus == 'cancel' || $transactionStatus == 'expire' || $transactionStatus == 'deny') {
            $order->update(['status' => 'unpaid']);
        }

        // Beri respons sukses ke Midtrans agar tidak mengirim notifikasi berulang
        return response()->json(['message' => 'Notification handled successfully.']);
    }
}