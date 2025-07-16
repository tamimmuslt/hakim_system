<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Prescriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PrescriptionsController extends Controller
{
    
    public function index()
    {
        $prescriptions = Prescriptions::with(['doctor', 'record'])->get();
        return response()->json($prescriptions);
    }

    
    public function store(Request $request)
    {
         $user = Auth::user();
        if ($user->user_type !== 'Doctor' || !$user->doctor) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'record_id'       => 'required|exists:medical_records,record_id',
            'doctor_id'       => 'required|exists:doctors,doctor_id',
            'medication_name' => 'required|string',
            'dosage'          => 'required|string',
            'instructions'    => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $prescription = Prescriptions::create($validator->validated());
        return response()->json($prescription, 201);
    }

   
    public function show($id)
    {
        $prescription = Prescriptions::with(['doctor', 'record'])->find($id);

        if (!$prescription) {
            return response()->json(['message' => 'Prescription not found'], 404);
        }

        return response()->json($prescription);
    }

   
    public function update(Request $request, $id)
    {
        $prescription = Prescriptions::find($id);

        if (!$prescription) {
            return response()->json(['message' => 'Prescription not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'medication_name' => 'sometimes|required|string',
            'dosage'          => 'sometimes|required|string',
            'instructions'    => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $prescription->update($validator->validated());

        return response()->json($prescription);
    }

    /**
     * حذف وصفة طبية.
     */
    public function destroy($id)
    {
        $prescription = Prescriptions::find($id);

        if (!$prescription) {
            return response()->json(['message' => 'Prescription not found'], 404);
        }

        $prescription->delete();

        return response()->json(['message' => 'Prescription deleted successfully']);
    }
}
