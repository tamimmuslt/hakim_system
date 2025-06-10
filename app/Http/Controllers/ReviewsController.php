<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reviews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewsController extends Controller
{
    /**
     * عرض جميع التقييمات مع معلومات المستخدم.
     */
    public function index()
    {
        $reviews = Reviews::with('user')->get();
        return response()->json($reviews);
    }

    /**
     * إنشاء تقييم جديد.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'         => 'required|exists:users,user_id',
            'reviewable_type' => 'required|string',
            'reviewable_id'   => 'required|integer',
            'rating'          => 'required|integer|min:1|max:5',
            'comment'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $review = Reviews::create($validator->validated());
        return response()->json($review, 201);
    }

    /**
     * عرض تقييم محدد.
     */
    public function show($id)
    {
        $review = Reviews::with('user')->find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        return response()->json($review);
    }

    /**
     * حذف تقييم.
     */
    public function destroy($id)
    {
        $review = Reviews::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->delete();
        return response()->json(['message' => 'Review deleted successfully']);
    }
}
