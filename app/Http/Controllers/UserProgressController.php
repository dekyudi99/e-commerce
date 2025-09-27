<?php

namespace App\Http\Controllers;

use App\Models\UserProgress;
use App\Models\Contens;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserProgressController extends Controller
{
    public function index($id) {
        $id_user = Auth::id();
        $currentTime = Carbon::now();
        $id_content = Contens::whereId($id)->first();

        if (!$id_content) {
            return response()->json([
                'success' => false,
                'massage' => 'Video tidak ada',
            ]);
        }

        $userProgress = UserProgress::create([
            "user_id" => $id_user,
            "content_id" => $id_content->id,
            "compleated_at" => $currentTime,
        ]);

        if ($userProgress) {
            return response()->json([
                'success' => true,
                'massage' => 'Progress Berhasil Disimpan',
                'data'    => $userProgress,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'massage' => 'Progress Gagal Disimpan',
            ]);
        }
    }
}