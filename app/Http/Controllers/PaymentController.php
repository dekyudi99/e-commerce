<?php
namespace App\Http\Controllers;

use App\Models\Orders;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function createMidtransTransaction($id)
    {
        // Atur konfigurasi Midtrans dari file .env
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Ambil data pesanan dari database
        $order = Orders::findOrFail($id);

        // Buat parameter transaksi untuk Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $order->invoice_number, // ID unik untuk setiap pesanan
                'gross_amount' => $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ],
        ];

        try {
            // Dapatkan URL pembayaran dari Midtrans Snap
            $paymentUrl = Snap::createTransaction($params)->redirect_url;

            // Kembalikan URL ini ke aplikasi Flutter
            return response()->json(['payment_url' => $paymentUrl]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}