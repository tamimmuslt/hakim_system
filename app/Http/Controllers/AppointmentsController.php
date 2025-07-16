<?php

namespace App\Http\Controllers;

use App\Models\Appointments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentsController extends Controller
{
    public function index()
    {
        $appointments = Appointments::with(['user', 'doctor','service'])->get();
        return response()->json($appointments);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,user_id',
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'service_id' => 'required|exists:services,service_id',
            'appointment_datetime' => 'required|date',
            'status' => 'required|in:scheduled,completed,cancelled,no_show',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $appointment = Appointments::create($request->all());
        return response()->json($appointment, 201);
    }

    public function show($id)
    {
        $appointment = Appointments::with(['user', 'doctor','service'])->find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        return response()->json($appointment);
    }

  
    public function update(Request $request, $id)
    {
        $appointment = Appointments::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|exists:users,user_id',
            'doctor_id' => 'sometimes|exists:doctors,doctor_id',
            'service_id' => 'sometimes|exists:services,service_id',
            'appointment_datetime' => 'sometimes|date',
            'status' => 'sometimes|in:scheduled,completed,cancelled,no_show',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $appointment->update($request->all());
        return response()->json($appointment);
    }

   
    public function destroy($id)
    {
        $appointment = Appointments::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        $appointment->delete();
        return response()->json(['message' => 'Appointment deleted']);
    }
}
