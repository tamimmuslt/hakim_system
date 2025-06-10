<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RadiologyImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RadiologyImagesController extends Controller
{
    /**
     * عرض كل صور الأشعة مع العلاقات.
     */
    public function index()
    {
        $images = RadiologyImages::with(['record', 'uploader'])->get();
        return response()->json($images);
    }

    /**
     * إنشاء صورة أشعة جديدة مع رفع الملف.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'record_id'   => 'required|exists:medical_records,record_id',
            'uploaded_by' => 'required|exists:users,user_id',
            'image_file'  => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // رفع الصورة إلى مجلد "radiology"
        $path = $request->file('image_file')->store('radiology', 'public');

        $image = RadiologyImages::create([
            'record_id'   => $request->record_id,
            'uploaded_by' => $request->uploaded_by,
            'image_url'   => Storage::url($path),
            'description' => $request->description
        ]);

        return response()->json($image, 201);
    }

    /**
     * عرض صورة أشعة محددة.
     */
    public function show($id)
    {
        $image = RadiologyImages::with(['record', 'uploader'])->find($id);

        if (!$image) {
            return response()->json(['message' => 'Radiology image not found'], 404);
        }

        return response()->json($image);
    }

    /**
     * تعديل بيانات صورة الأشعة (الوصف مثلاً أو إعادة رفع الصورة).
     */
    public function update(Request $request, $id)
    {
        $image = RadiologyImages::find($id);

        if (!$image) {
            return response()->json(['message' => 'Radiology image not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'image_file'  => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // حذف الملف القديم إذا تم رفع ملف جديد
        if ($request->hasFile('image_file')) {
            if ($image->image_url) {
                $oldPath = str_replace('/storage/', '', $image->image_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image_file')->store('radiology', 'public');
            $data['image_url'] = Storage::url($path);
        }

        $image->update($data);
        return response()->json($image);
    }

    /**
     * حذف صورة أشعة.
     */
    public function destroy($id)
    {
        $image = RadiologyImages::find($id);

        if (!$image) {
            return response()->json(['message' => 'Radiology image not found'], 404);
        }

        // حذف الملف من التخزين
        if ($image->image_url) {
            $path = str_replace('/storage/', '', $image->image_url);
            Storage::disk('public')->delete($path);
        }

        $image->delete();

        return response()->json(['message' => 'Radiology image deleted successfully']);
    }
}
