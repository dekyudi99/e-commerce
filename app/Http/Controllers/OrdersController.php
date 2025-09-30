<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\Order_Item;
use App\Models\Products;
use App\Models\Cart;
use App\Models\Shop;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PaymentController;

use function PHPSTORM_META\map;

class OrdersController extends Controller
{
    // Menambahkan produk ke cart
    public function cart(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Quantity wajib diisi',
            ], 422);
        }

        $idUser = Auth::id();
        $quantity = $request->input('quantity');

        $cart = Cart::where('user_id', $idUser)
                ->where('product_id', $id)
                ->first();
        
        if ($cart) {
            $cart->increment('quantity', $quantity);
            
            return response()->json([
                'success' => true,
                'message' => 'Kuantitas produk di keranjang berhasil diperbarui',
                'data' => $cart,
            ], 200);

        } else {
            $newCartItem = Cart::create([
                'user_id' => $idUser,
                'product_id' => $id,
                'quantity' => $quantity,
            ]);

            if (!$newCartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk gagal ditambahkan ke keranjang',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan ke keranjang',
                'data' => $newCartItem,
            ], 201);
        }
    }

    // Melihat Keranjang saya
    public function mycart() {
        $id = Auth::id();
        $cart = Cart::where('user_id', $id)->with(['product', 'user'])->get();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data cart',
            ], 500);
        }

        $customCart = $cart->map(function($item) {
            return [
                'id' => $item->product_id,
                'name' => $item->product->title,
                'price' => $item->product->price,
                'farmer' => $item->user->name,
                'amount' => $item->quantity,
                'image' => $item->product->image,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data cart',
            'data' => $customCart,
        ]);
    }

    public function orderCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_ids'       => 'required|array',
            'cart_ids.*'     => 'exists:cart,id',
            'shipping_address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $idUser = Auth::id();
        $selectedCartIds = $request->input('cart_ids');

        // 2. Ambil HANYA item yang dipilih oleh pengguna
        // Gunakan whereIn() untuk mencocokkan dengan array ID yang dikirim
        $cartItems = Cart::where('user_id', $idUser)
                        ->whereIn('id', $selectedCartIds)
                        ->with('product')
                        ->get();

        // Validasi: pastikan item yang dipilih benar-benar ada dan milik pengguna
        if ($cartItems->count() != count($selectedCartIds)) {
            return response()->json(['message' => 'Beberapa item tidak valid atau bukan milik Anda.'], 400);
        }

        try {
            $order = DB::transaction(function () use ($cartItems, $idUser, $request, $selectedCartIds) {
                
                // Proses pembuatan Orders dan Orders_Item (LOGIKANYA TETAP SAMA)
                $totalAmount = 0;
                foreach ($cartItems as $item) {
                    if (!$item->product || $item->product->stock < $item->quantity) {
                        throw new \Exception('Stok produk ' . $item->product->name . ' tidak mencukupi.');
                    }
                    $totalAmount += $item->product->price * $item->quantity;
                }

                $newOrder = Orders::create([
                    'user_id' => $idUser,
                    'invoice_number' => 'INV-' . time(),
                    'total_amount' => $totalAmount,
                    'shipping_address' => $request->input('shipping_address'),
                    'status' => 'unpaid',
                ]);

                foreach ($cartItems as $item) {
                    Order_Item::create([
                        'order_id' => $newOrder->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'name_at_purchase' => $item->product->title,
                        'price_at_purchase' => $item->product->price,
                        'description_at_purchase' => $item->product->description,
                        'subtotal' => $item->quantity * $item->product->price,
                    ]);
                    $item->product->decrement('stock', $item->quantity);
                }
                
                // 3. Hapus HANYA item yang sudah di-checkout dari keranjang
                Cart::whereIn('id', $selectedCartIds)->delete();

                return $newOrder;
            });

            // 1. Buat instance dari PaymentController
            $paymentController = new PaymentController();

            // 2. Panggil fungsi untuk membuat transaksi Midtrans, kirim ID order yang baru dibuat
            $paymentResponse = $paymentController->createMidtransTransaction($order->id);

            // 3. Ambil konten dari response JSON yang dikembalikan oleh PaymentController
            $paymentData = json_decode($paymentResponse->getContent(), true);

            // Ambil URL pembayaran, atau null jika terjadi error
            $paymentUrl = $paymentData['payment_url'] ?? null;

            // 4. Modifikasi response untuk menyertakan payment_url
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat. Lanjutkan ke pembayaran.',
                'order' => $order,
                'payment_url' => $paymentUrl // <-- TAMBAHKAN INI
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function directOrder(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:1',
            'shipping_address' => 'required|string',
        ]);

        if ($validator->fails()) {
            // Respons validasi yang lebih standar
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $idUser = Auth::id();
        $quantity = $request->input('quantity');

        // 1. Cek apakah produk ada
        $product = Products::find($id);
        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan.'], 404);
        }

        // 2. Cek stok produk
        if ($product->stock < $quantity) {
            return response()->json(['message' => 'Stok produk tidak mencukupi.'], 400);
        }

        try {
            // 3. Gunakan Database Transaction
            $order = DB::transaction(function () use ($idUser, $product, $quantity, $request) {
                
                // Buat record di tabel Orders
                $newOrder = Orders::create([
                    'user_id' => $idUser,
                    'invoice_number' => 'INV-' . time(),
                    'total_amount' => $quantity * $product->price,
                    'shipping_address' => $request->input('shipping_address'),
                    'status' => 'unpaid',
                ]);

                // Buat record di tabel Order_Item
                Order_Item::create([
                    'order_id' => $newOrder->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'name_at_purchase' => $product->title, 
                    'price_at_purchase' => $product->price,
                    'description_at_purchase' => $product->description,
                    'subtotal' => $quantity * $product->price,
                ]);

                // 4. Kurangi stok produk
                $product->decrement('stock', $quantity);
                
                return $newOrder; // Kembalikan order yang baru dibuat
            });

            // 1. Buat instance dari PaymentController
            $paymentController = new PaymentController();

            // 2. Panggil fungsi untuk membuat transaksi Midtrans
            $paymentResponse = $paymentController->createMidtransTransaction($order->id);

            // 3. Ambil konten dari response JSON
            $paymentData = json_decode($paymentResponse->getContent(), true);
            
            // Ambil URL pembayaran, atau null jika terjadi error
            $paymentUrl = $paymentData['payment_url'] ?? null;

            // 4. Modifikasi response untuk menyertakan payment_url
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat. Lanjutkan ke pembayaran.',
                'order' => $order,
                'payment_url' => $paymentUrl // <-- TAMBAHKAN INI
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function myorder() {
        $id = Auth::id();
        $cart = Orders::where('user_id', $id)
                   ->orderBy('created_at', 'desc') // Urutkan dari yang terbaru
                   ->get();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pesanan',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menampilkan pesanan anda',
            'data' => $cart,
        ]);
    }

    // untuk admin melihat semua pesanan
    public function orders() {
        $orders = Orders::all();

        if (!$orders) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menampilkan seluruh pesanan',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menampilkan semua data',
            'data' => $orders,
        ]);
    }

    public function orderIn()
    {
        // $product sekarang adalah KOLEKSI produk
        $product = auth()->user()->product;

        // Cek jika koleksinya kosong
        if ($product->isEmpty()) {
            return response()->json(['data' => []]);
        }

        // 1. Ambil semua ID produk langsung dari koleksi
        $productIds = $product->pluck('id')->toArray(); // <-- PERBAIKAN DI SINI

        // 2. Cari semua order_id unik yang memiliki item produk dari toko ini
        $orderIds = \App\Models\Order_Item::whereIn('product_id', $productIds)
                                        ->distinct()
                                        ->pluck('order_id');

        // 3. Ambil data pesanan lengkap berdasarkan ID yang sudah didapat
        $orders = \App\Models\Orders::whereIn('id', $orderIds)
                                        ->orderBy('created_at', 'desc')
                                        ->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Berhasil menampilkan pesanan yang masuk',
            'data'    => $orders,
        ], 200);
    }
}