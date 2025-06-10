<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromotionsController extends Controller
{
    /**
     * عرض كل العروض الترويجية مع المركز المرتبط.
     */
    public function index()
    {
        $promotions = Promotions::with('center')->get();
        return response()->json($promotions);
    }

    /**
     * إنشاء عرض ترويجي جديد.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'center_id'           => 'required|exists:centers,center_id',
            'title'               => 'required|string|max:150',
            'description'         => 'required|string',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'discount_percent'    => 'required|numeric|min:0|max:100',
            'price_after_discount'=> 'required|numeric|min:0',
            'is_active'           => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $promotion = Promotions::create($validator->validated());
        return response()->json($promotion, 201);
    }

    /**
     * عرض عرض ترويجي معين.
     */
    public function show($id)
    {
        $promotion = Promotions::with('center')->find($id);

        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }

        return response()->json($promotion);
    }

    /**
     * تعديل عرض ترويجي.
     */
    public function update(Request $request, $id)
    {
        $promotion = Promotions::find($id);

        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
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

    /**
     * حذف عرض ترويجي.
     */
    public function destroy($id)
    {
        $promotion = Promotions::find($id);

        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }

        $promotion->delete();

        return response()->json(['message' => 'Promotion deleted successfully']);
    }
}
