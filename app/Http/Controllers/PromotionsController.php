<?php

namespace App\Http\Controllers;

use App\Models\Promotions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PromotionsController extends Controller
{
    // عرض كل العروض النشطة
    public function index()
    {
        $promotions = Promotions::with('center')->where('is_active', true)->get();
        return response()->json($promotions);
    }

    // إضافة عرض جديد
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->user_type !== 'Center') {
            return response()->json(['message' => 'Only centers can create promotions'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title'                => 'required|string|max:150',
            'description'          => 'required|string',
            'start_date'           => 'required|date',
            'end_date'             => 'required|date|after_or_equal:start_date',
            'discount_percent'     => 'required|numeric|min:0|max:100',
            'price_after_discount' => 'required|numeric|min:0',
            'image'                => 'sometimes|image|mimes:jpg,jpeg,png|max:2048', // صورة اختيارية
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['center_id'] = $user->center->center_id;
        $data['is_active'] = false; // تبدأ غير مفعلة

        // رفع الصورة إذا كانت موجودة
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('promotions', 'public');
        }

        $promotion = Promotions::create($data);

        return response()->json([
            'message'   => 'Promotion created and awaiting admin approval',
            'promotion' => $promotion
        ], 201);
    }

    // عرض عرض معين
    public function show($id)
    {
        $promotion = Promotions::with('center')->find($id);

        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }

        return response()->json($promotion);
    }

    // تعديل عرض
    public function update(Request $request, $id)
    {
        $user = Auth::user();
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
            'title'                => 'sometimes|required|string|max:150',
            'description'          => 'sometimes|required|string',
            'start_date'           => 'sometimes|required|date',
            'end_date'             => 'sometimes|required|date|after_or_equal:start_date',
            'discount_percent'     => 'sometimes|required|numeric|min:0|max:100',
            'price_after_discount' => 'sometimes|required|numeric|min:0',
            'is_active'            => 'sometimes|boolean',
            'image'                => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // رفع الصورة إذا كانت موجودة
        if ($request->hasFile('image')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($promotion->image && Storage::disk('public')->exists($promotion->image)) {
                Storage::disk('public')->delete($promotion->image);
            }

            $data['image'] = $request->file('image')->store('promotions', 'public');
        }

        $promotion->update($data);

        return response()->json($promotion);
    }

    // حذف عرض
    public function destroy($id)
    {
        $user = Auth::user();
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

        // حذف الصورة من التخزين إذا موجودة
        if ($promotion->image && Storage::disk('public')->exists($promotion->image)) {
            Storage::disk('public')->delete($promotion->image);
        }

        $promotion->delete();

        return response()->json(['message' => 'Promotion deleted successfully']);
    }
}
