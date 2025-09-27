<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductsController extends Controller
{
    public function index()
    {
        $product = Products::all();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal',
            ], 200);
        } else {
            return response()->json([
                'success' => true,
                'massage' => 'List Semua Product',
                'data' => $product,
            ]);
        }
    }

    public function myProducts()
    {
        $id = Auth::id();
        $user = User::whereId($id)->first();
        $product = Products::where('shop_id', $user->shop->id)->get();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal',
            ], 200);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'List Semua Product Anda',
                'data' => $product,
            ]);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'        => 'required',
            'description'  => 'required',
            'location'     => 'required',
            'category'     => 'required',
            'price'        => 'required|numeric',
            'stock'        => 'required|numeric',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal!',
                'data'    => $validator->errors()
            ], 422);
        } else {
            $id = Auth::id();
            $user = User::whereId($id)->first();

            if (!$user->shop || $user->shop->status !== "Telah Terverifikasi") {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus memiliki toko yang sudah terverifikasi untuk melakukan aksi ini.',
                ], 403);
            }

            $imagePathForDb = 'default.png'; // Gambar default jika tidak ada upload
            if ($request->hasFile('image')) {
                $imageFile = $request->file('image');
                // Buat nama file yang unik
                $imageName = time() . '_' . $imageFile->getClientOriginalName();

                // Pindahkan file ke public/uploads/product
                $imageFile->move('uploads/product', $imageName);

                // Ini adalah nama file yang akan Anda simpan di kolom 'image' database
                $imagePathForDb = $imageName;
            }

            $product = Products::create([
                'shop_id'     => $user->shop->id,
                'title'       => $request->input('title'),
                'description' => $request->input('description'),
                'location'    => $request->input('location'),
                'category'    => $request->input('category'),
                'price'       => $request->input('price'),
                'stock'       => $request->input('stock'),
                'image'       => $imagePathForDb,
            ]);

            if ($product) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product Berhasil Disimpan!',
                    'data'    => $product,
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Product Gagal Disimpan!',
                ], 400);
            }
        }
    }

    public function show($id)
    {
        $product = Products::find($id);

        if ($product) {
            return response()->json([
                'success'   => true,
                'message'   => 'Detail Product!',
                'data'      => $product,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Product Tidak Ditemukan!',
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $product = Products::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product tidak ditemukan!',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title'        => 'required',
            'description'  => 'required',
            'location'     => 'required',
            'category'     => 'required',
            'price'        => 'required|numeric',
            'stock'        => 'required|numeric',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $imagePathForDb = $product->image; // Ambil nama gambar yang sudah ada

        if ($request->hasFile('image')) {
            // 1. Hapus gambar lama jika ada (dan bukan gambar default)
            if ($product->image && $product->image != 'default.png') {
                $oldImagePath = ('uploads/product/' . $product->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // 2. Simpan gambar baru (logika sama seperti store)
            $imageFile = $request->file('image');
            $imageName = time() . '_' . $imageFile->getClientOriginalName();
            $imageFile->move('uploads/product', $imageName);
            
            $imagePathForDb = $imageName; // Update dengan nama file baru
        }

        $id_user = Auth::id();

        $productData = [
            'user_id'     => $id_user,
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'location'    => $request->input('location'),
            'category'    => $request->input('category'),
            'price'       => $request->input('price'),
            'stok'        => $request->input('stok'),
            'image'       => $imagePathForDb,
        ];

        $updated = $product->update($productData);

        if ($updated) {
            $updatedProduct = Products::find($id);
            return response()->json([
                'success' => true,
                'message' => 'Product Berhasil Diupdate!',
                'data'    => $updatedProduct,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Product Gagal Diupdate!',
            ], 500); 
        }
    }

    public function destroy($id)
    {
        $product = Products::whereId($id)->first();
        $product->delete();

        if ($product) {
            return response()->json([
                'success' => true,
                'message' => 'Product Berhasil Dihapus!',
            ], 200);
        }
    }
}