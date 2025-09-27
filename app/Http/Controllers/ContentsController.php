<?php

namespace App\Http\Controllers;

use App\Models\Contens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContentsController extends Controller
{
    public function index()
    {
        $contents = Contens::all();

        if (!$contents) {
            return response()->json([
                'success' => false,
                'massage' => 'Gagal',
            ]);
        } else {
            return response()->json([
                'success' => true,
                'massage' => 'List semua contents',
                'data' => $contents,
            ]);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required',
            'video'       => 'required',
            'description' => 'required',
            'creator'     => 'required',
            'playlist'    => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Semua Kolom Wajib Diisi!',
                'data'    => $validator->errors()
            ], 401);
        } else {
            $content = Contens::create([
                'title'       => $request->input('title'),
                'video'       => $request->input('video'),
                'description' => $request->input('description'),
                'creator'     => $request->input('creator'),
                'playlist'    => $request->input('playlist'),
                'thumbnail'   => 'https://img.youtube.com/vi/'.$request->input('video').'/maxresdefault.jpg',
            ]);

            if ($content) {
                return response()->json([
                    'success' => true,
                    'message' => 'Konten Berhasil Disimpan!',
                    'data'    => $content,
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Konten Gagal Disimpan!',
                ], 400);
            }
        }
    }

    public function show($id) {
        $content = Contens::find($id);

        if ($content) {
            return response()->json([
                'success'   => true,
                'message'   => 'Detail Konten!',
                'data'      => $content,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Konten Tidak Ditemukan!',
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $content = Contens::find($id);

        if (!$content) {
            return response()->json([
                'success' => false,
                'message' => 'Konten tidak ditemukan!',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title'        => 'required',
            'video'        => 'required',
            'description'  => 'required',
            'creator'      => 'required',
            'playlist'     => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $contentData = [
            'title'        => $request->input('title'),
            'video'        => $request->input('video'),
            'description'  => $request->input('description'),
            'creator'      => $request->input('creator'),
            'playlist'    => $request->input('playlist'),
            'thumbnail'   => 'https://img.youtube.com/vi/'.$request->input('video').'/maxresdefault.jpg',
        ];

        $updated = $content->update($contentData);

        if ($updated) {
            $updatedContent = Contens::find($id);
            return response()->json([
                'success' => true,
                'message' => 'Konten Berhasil Diupdate!',
                'data'    => $updatedContent,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Konten Gagal Diupdate!',
            ], 500); 
        }
    }

    public function destroy($id)
    {
        $content = Contens::whereId($id)->first();
        $content->delete();

        if ($content) {
            return response()->json([
                'success' => true,
                'message' => 'Konten Berhasil Dihapus!',
            ], 200);
        }
    }
}