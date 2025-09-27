<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Orders;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    public function show() {
        $id = Auth::id();
        $shop = Shop::where('user_id', $id)->first();
        
        if ($shop) {
            return response()->json([
                'success' => true,
                'message' => 'Berhasil memngambil data toko',
                'data' => $shop,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data'
            ], 500);
        }
    }

    public function showAll() {
        $shop = Shop::with('user')->get();
        
        if ($shop) {
            return response()->json([
                'success' => true,
                'message' => 'Berhasil memngambil data toko',
                'data' => $shop,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data'
            ], 500);
        }
    }

    public function showDetail($id) {
        $shop = Shop::with('user')->find($id);
        
        if ($shop) {
            return response()->json([
                'success' => true,
                'message' => 'Berhasil memngambil data toko',
                'data' => $shop,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data'
            ], 500);
        }
    }

    public function store(Request $request) {
        $id = Auth::id();

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'foto_ktp' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $shop = Shop::where('user_id', $id)->first();
        
        if ($shop) {
            return response()->json([
                'success' => false,
                'message' => 'Lu udah punya toko anjir',
            ], 409);
        }

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'massage' => 'Form belum lengkap',
            ], 422);
        } else {
            $imagePath = null;
            if ($request->hasFile('foto_ktp')) {
                $imageName = Str::random(34) . '.' . $request->file('foto_ktp')->getClientOriginalExtension();
                $request->file('foto_ktp')->move('uploads/foto_ktp', $imageName);
                $imagePath = $imageName;
            }

            $shop = Shop::create([
                'user_id' => $id,
                'name' => $request->input('name'),
                'foto_ktp' => $imagePath,
            ]);

            if ($shop) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pengajuan Toko Berhasil',
                    'data'    => $shop,
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan toko gagal',
                ], 400);
            }
        }
    }

    public function update(Request $request) {
        $userId = Auth::id();
        $shop = Shop::where('user_id', $userId)->first();

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'fotoProfil' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'massage' => 'Form belum lengkap',
                'error' => $validator->errors(),
            ], 422);
        } else {
            $imagePathForDb = null; // Ambil nama gambar yang sudah ada

            if ($request->hasFile('fotoProfil')) {
                // 1. Hapus gambar lama jika ada (dan bukan gambar default)
                if ($shop->fotoProfil_toko && $shop->fotoProfil_toko != 'default.png') {
                    $oldImagePath = ('uploads/toko/' . $shop->fotoProfil_toko);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // 2. Simpan gambar baru (logika sama seperti store)
                $imageFile = $request->file('fotoProfil');
                $imageName = time() . '_' . $imageFile->getClientOriginalName();
                $imageFile->move('uploads/toko', $imageName);
                
                $imagePathForDb = $imageName; // Update dengan nama file baru
            }

            $shop->update([
                'name' => $request->input('name'),
                'foto_ktp' => $shop->foto_ktp,
                'fotoProfil_toko' => $imagePathForDb,
            ]);

            if ($shop) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profil toko berhasil diupdate',
                    'data'    => $shop,
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Update toko gagal',
                ], 400);
            }
        }
    }

    // Untuk admin memvalidasi toko
    public function validasi(Request $request, $id) {
        $shop = Shop::whereId($id)->first();
        $validator = Validator::make($request->all(), [
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'massage' => 'Status wajib diisi',
                'errors'  => $validator->errors(),
            ], 422);
        } else {
            $shop->update([
                'user_id' => $shop->user_id,
                'name' => $shop->name,
                'foto_ktp' => $shop->foto_ktp,
                'status' => $request->input('status'),
            ]);

            if ($shop) {
                return response()->json([
                    'success' => true,
                    'message' => 'Toko Telah Diverivikasi',
                    'data'    => $shop,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Toko Gagal Diverifikasi',
                ], 400);
            }
        }
    }

    public function destroy($id) {
        $shop = Shop::whereId($id);

        $shop->delete();

        if ($shop) {
            return response()->json([
                'success' => true,
                'message' => 'Toko Berhasil Dihapus!',
            ], 200);
        }
    }
}