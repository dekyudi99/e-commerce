<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        $product = Products::where('user_id', $id)->get();

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
            'satuan'       => ['required', Rule::in(['kg', 'g', 'biji', 'ikat', 'tandan', 'liter'])],
            'image'       => 'nullable|array|max:5',
            'image.*'     => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal!',
                'data'    => $validator->errors()
            ], 422);
        } else {

            $imagePaths = [];

            if ($request->hasFile('image')) {
                foreach ($request->file('image') as $imageFile) {
                    $imageName = Str::uuid()->toString() . '.' . $imageFile->getClientOriginalExtension();
                    $imageFile->move('uploads/product', $imageName);
                    $imagePaths[] = $imageName;
                }
            }

            // Jika tidak ada upload, pakai default
            if (empty($imagePaths)) {
                $imagePaths[] = 'default.png';
            }

            $product = Products::create([
                'user_id'     => Auth::id(),
                'title'       => $request->input('title'),
                'description' => $request->input('description'),
                'location'    => $request->input('location'),
                'category'    => $request->input('category'),
                'price'       => $request->input('price'),
                'stock'       => $request->input('stock'),
                'satuan'      => $request->input('satuan'),
                'image'      => $imagePaths,
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
        $product = Products::with('user')->find($id);

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
        $user_id= Auth::id();

        if ($user_id!=$product->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak berhak mengedit produk ini',
            ], 422);
        }

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
            'image'       => 'nullable|array|max:5',
            'image.*'     => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $imagePaths = json_decode($product->images, true) ?? [];

        if ($request->hasFile('image')) {
            // Hapus gambar lama (jika bukan default)
            foreach ($imagePaths as $oldImage) {
                if ($oldImage !== 'default.png' && file_exists('uploads/product/' . $oldImage)) {
                    unlink('uploads/product/' . $oldImage);
                }
            }

            $imagePaths = []; // reset
            foreach ($request->file('image') as $imageFile) {
                $imageName = Str::uuid()->toString() . '.' . $imageFile->getClientOriginalExtension();
                $imageFile->move('uploads/product', $imageName);
                $imagePaths[] = $imageName;
            }
        }

        $product->update([
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'location'    => $request->input('location'),
            'category'    => $request->input('category'),
            'price'       => $request->input('price'),
            'stock'       => $request->input('stock'),
            'satuan'      => $request->input('satuan'),
            'image'       => $imagePaths,
        ]);


        if ($product) {
            return response()->json([
                'success' => true,
                'message' => 'Product Berhasil Diupdate!',
                'data'    => $product,
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
        $user_id= Auth::id();
        
        if ($user_id!=$product->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak berhak menghapus produk ini',
            ], 422);
        }

        $product->delete();

        if ($product) {
            return response()->json([
                'success' => true,
                'message' => 'Product Berhasil Dihapus!',
            ], 200);
        }
    }
}