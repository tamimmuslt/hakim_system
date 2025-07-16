<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Promotions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromotionsController extends Controller
{
    
    public function index()
    {
$promotions = Promotions::with('center')->where('is_active', true)->get();
        return response()->json($promotions);
    }

   
    public function store(Request $request)
{
    $user = auth::user();
    if ($user->user_type !== 'Center') {
        return response()->json(['message' => 'Only centers can create promotions'], 403);
    }

    $validator = Validator::make($request->all(), [
        'title'               => 'required|string|max:150',
        'description'         => 'required|string',
        'start_date'          => 'required|date',
        'end_date'            => 'required|date|after_or_equal:start_date',
        'discount_percent'    => 'required|numeric|min:0|max:100',
        'price_after_discount'=> 'required|numeric|min:0',
         //'is_active' => 'sometimes|boolean',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $promotion = Promotions::create(array_merge(
        $validator->validated(),
        [
            'center_id' => $user->center->center_id,
            'is_active' => false  
        ]
    ));

    return response()->json([
        'message' => 'Promotion created and awaiting admin approval',
        'promotion' => $promotion
    ], 201);
}

 
    public function show($id)
    {
        $promotion = Promotions::with('center')->find($id);

        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }

        return response()->json($promotion);
    }

   
    public function update(Request $request, $id)
    {
        $user = auth::user();
        if ($user->user_type !== 'Center') {
            return response()->json(['message' => 'Only centers can update promotions'], 403);
        }

        $promotion = Promotions::find($id);

        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }

    
        if ($promotion->center_id !== $user->center->center_id) {
            return response()->json(['message' => 'This promotion does not belong to your center'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title'               => 'sometimes|required|string|max:150',
            'description'         => 'sometimes|required|string',
            'start_date'          => 'sometimes|required|date',
            'end_date'            => 'sometimes|required|date|after_or_equal:start_date',
            'discount_percent'    => 'sometimes|required|numeric|min:0|max:100',
            'price_after_discount'=> 'sometimes|required|numeric|min:0',
            'is_active'           => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $promotion->update($validator->validated());

        return response()->json($promotion);
    }

    public function destroy($id)
    {
        $user = auth::user();
        if ($user->user_type !== 'Center') {
            return response()->json(['message' => 'Only centers can delete promotions'], 403);
        }

        $promotion = Promotions::find($id);

        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }

        if ($promotion->center_id !== $user->center->center_id) {
            return response()->json(['message' => 'This promotion does not belong to your center'], 403);
        }

        $promotion->delete();

        return response()->json(['message' => 'Promotion deleted successfully']);
    }
}
