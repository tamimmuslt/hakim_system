<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\RadiologyImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RadiologyImagesController extends Controller
{
   
    public function index()
    {
        $images = RadiologyImages::with(['record', 'uploader.doctor'])->get();
        return response()->json($images);
    }

    
    public function store(Request $request)
    {
            $user = Auth::user();

        if ($user->user_type !== 'Doctor' || !$user->doctor?->is_approved) {
    return response()->json(['message' => 'Only  doctors can upload data'], 403);
}
  
        $validator = Validator::make($request->all(), [
            'record_id'   => 'required|exists:medical_records,record_id',
            'image_url'  => 'required|image|mimes:jpeg,png,jpg,gif,pdf|max:2048',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if ($user->user_type !== 'Doctor' || !$user->doctor?->is_approved) {
            return response()->json(['message' => 'Only approved doctors can upload data'], 403);
        }

        $path = $request->file('image_url')->store('radiology', 'public');

        $image = RadiologyImages::create([
            'record_id'   => $request->record_id,
            'uploaded_by' => $user->user_id,
            'image_url'   => Storage::url($path),
            'description' => $request->description
        ]);

        return response()->json($image, 201);
    }

    
    public function show($id)
    {
        $image = RadiologyImages::with(['record', 'uploader.doctor'])->find($id);

        if (!$image) {
            return response()->json(['message' => 'Radiology image not found'], 404);
        }

        return response()->json($image);
    }

   
    public function update(Request $request, $id)
    {
            $user = Auth::user();

        if ($user->user_type !== 'Doctor' || !$user->doctor?->is_approved) {
    return response()->json(['message' => 'Only  doctors can upload data'], 403);
}
  
        $image = RadiologyImages::find($id);

        if (!$image) {
            return response()->json(['message' => 'Radiology image not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'image_url'=> 'sometimes|image|mimes:jpeg,png,jpg,gif,pdf|max:2048',
            'description' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('image_url')) {
    if ($image->image_url) {
        \App\Models\RadiologyImageVersion::create([
            'radiology_image_id' => $image->image_id,
            'image_url' => $image->image_url,
            'saved_at' => now(),
        ]);

        $oldPath = str_replace('/storage/', '', $image->image_url);
        Storage::disk('public')->delete($oldPath);
    }

    $path = $request->file('image_url')->store('radiology', 'public');
    $data['image_url'] = Storage::url($path);
}

        $image->update($data);
        return response()->json($image);
    }

  
    public function destroy($id)
    {
        $image = RadiologyImages::find($id);

        if (!$image) {
            return response()->json(['message' => 'Radiology image not found'], 404);
        }

        if ($image->image_url) {
            $path = str_replace('/storage/', '', $image->image_url);
            Storage::disk('public')->delete($path);
        }

        $image->delete();

        return response()->json(['message' => 'Radiology image deleted successfully']);
    }
}
