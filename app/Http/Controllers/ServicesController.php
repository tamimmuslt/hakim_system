<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Services;
use App\Models\Centers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ServicesController extends Controller
{
   
    public function index()
{
    $services = Services::with([
        'doctors.user', 
        'centers' => function ($query) {
            $query->select('centers.center_id')->withPivot('price');
        }
    ])->latest()->get();

    return response()->json([
        'success' => true,
        'data'    => $services
    ]);
}

    /**
     * إنشاء خدمة جديدة وربطها بالمركز الحالي مع تحديد السعر.
     */
    public function store(Request $request)
{
    $user = Auth::user();

    if (!$user || $user->user_type !== 'Center') {
        return response()->json([
            'success' => false,
            'message' => 'Only centers can create services'
        ], 403);
    }

    $validator = Validator::make($request->all(), [
        'name'=> 'required|string|max:100',
        'doctor_id'   => 'required|exists:doctors,doctor_id',   
        'description' => 'nullable|string|max:500',
        'price'       => 'required|numeric|min:0'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    $service = Services::create($request->only(['name', 'description']));

    $user->center->services()->attach($service->service_id, [
        'price' => $request->price
    ]);

    $service->load(['centers' => function ($query) {
        $query->select('centers.center_id')->withPivot('price');
    }]);

    return response()->json([
        'success' => true,
        'message' => 'Service created and linked to center successfully',
        'data'    => $service
    ], 201);
}

    /**
     * عرض خدمة معينة حسب ID مع السعر.
     */
    public function show($id)
    {
        $service = Services::with(['centers' => function ($query) {
            $query->select('centers.center_id', 'centers.user_id')->withPivot('price');
        }])->find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $service
        ]);
    }

    /**
     * تحديث بيانات خدمة.
     */
    public function update(Request $request, $id)
{
      $user = Auth::user();

    if (!$user || $user->user_type !== 'Center') {
        return response()->json([
            'success' => false,
            'message' => 'Only centers can create services'
        ], 403);
    }
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
        'price'       => 'required|numeric|min:0'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    $service->update($validator->validated());

    if ($request->has('price')) {
        $user = Auth::user();
        if ($user && $user->user_type === 'Center') {
            $user->center->services()->updateExistingPivot($service->id, [
                'price' => $request->price
            ]);
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Service updated successfully',
        'data'    => $service
    ]);
}

public function destroy($id)
{
    $service = Services::find($id);

    if (!$service) {
        return response()->json([
            'success' => false,
            'message' => 'Service not found'
        ], 404);
    }

    $service->centers()->detach();
    $service->delete();

    return response()->json([
        'success' => true,
        'message' => 'Service deleted successfully'
    ]);
}


public function checkServiceType($id)
{
    $service = Services::findOrFail($id);

    if ($service->requires_doctor) {
        return response()->json(['type' => 'appointment']);
    } else {
        return response()->json(['type' => 'service_booking']);
    }
}
}