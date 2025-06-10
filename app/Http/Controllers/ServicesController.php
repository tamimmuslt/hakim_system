<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServicesController extends Controller
{
    /**
     * عرض جميع الخدمات مع المراكز المرتبطة.
     */
    public function index()
    {
        $services = Services::with('centers')->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }

    /**
     * إنشاء خدمة جديدة.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $service = Services::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully',
            'data'    => $service
        ], 201);
    }

    /**
     * عرض خدمة معينة حسب ID.
     */
    public function show($id)
    {
        $service = Services::with('centers')->find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $service
        ]);
    }

    /**
     * تحديث خدمة.
     */
    public function update(Request $request, $id)
    {
        $service = Services::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $service->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully',
            'data'    => $service
        ]);
    }

    /**
     * حذف خدمة.
     */
    public function destroy($id)
    {
        $service = Services::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully'
        ]);
    }
}
