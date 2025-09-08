<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reviews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ReviewsController extends Controller
{
    public function index()
{
    $user = Auth::user();

    if ($user->role === 'doctor') {
        $doctor = $user->doctor; 
        $reviews = Reviews::with('user')
            ->where('reviewable_type', 'App\Models\Doctor')
            ->where('reviewable_id', $doctor->id)
            ->get();

    } elseif ($user->role === 'center') {
        $center = $user->center; 
        $reviews = Reviews::with('user')
            ->where('reviewable_type', 'App\Models\Center')
            ->where('reviewable_id', $center->id)
            ->get();

    } else {
        return response()->json(['message' => 'غير مسموح لك بمشاهدة التقييمات'], 403);
    }

    return response()->json($reviews);
}

   
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

    public function show($id)
    {
        $review = Reviews::with('user')->find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        return response()->json($review);
    }

    
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
