<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DoctorAvailability;
use Illuminate\Support\Facades\Validator;

class DoctorAvailabilityController extends Controller
{
    // عرض الأوقات لطبيب معين
    public function index($doctor_id)
    {
        $availability = DoctorAvailability::where('doctor_id', $doctor_id)->get();
        return response()->json($availability);
    }

    // إضافة وقت جديد
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id'   => 'required|exists:doctors,doctor_id',
            'day_of_week' => 'required|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $availability = DoctorAvailability::create($validator->validated());

        return response()->json($availability, 201);
    }

    // تعديل وقت
    public function update(Request $request, $id)
    {
        $availability = DoctorAvailability::find($id);

        if (!$availability) {
            return response()->json(['message' => 'Availability not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'day_of_week' => 'sometimes|required|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'start_time'  => 'sometimes|required|date_format:H:i',
            'end_time'    => 'sometimes|required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $availability->update($validator->validated());

        return response()->json($availability);
    }

    // حذف وقت
    public function destroy($id)
    {
        $availability = DoctorAvailability::find($id);

        if (!$availability) {
            return response()->json(['message' => 'Availability not found'], 404);
        }

        $availability->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
