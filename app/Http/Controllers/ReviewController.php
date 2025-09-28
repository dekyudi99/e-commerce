<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function store(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = Auth::id();
        $user = User::whereId($userId)->first();
        $productId = $id;

        $hasPurchased = $user->orders()
            ->where('status', 'paid')
            ->whereHas('items', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->exists(); 

        if (!$hasPurchased) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya bisa mereview produk yang sudah Anda beli dan bayar.',
            ], 403);
        }
        
        $alreadyReviewed = Review::where('user_id', $user->id)
                                ->where('product_id', $productId)
                                ->exists();

        if ($alreadyReviewed) {
             return response()->json([
                'success' => false,
                'message' => 'Anda sudah pernah memberikan review untuk produk ini.',
            ], 409);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review berhasil ditambahkan.',
            'data' => $review
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = Auth::id();
        $review = Review::whereId($id)->first();

        if ($userId != $review->user_id) {
             return response()->json([
                'success' => false,
                'message' => 'Anda tidak bisa mengedit review orang lain.',
            ], 409);
        }

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review berhasil diupdate.',
            'data' => $review
        ], 201);
    }
}