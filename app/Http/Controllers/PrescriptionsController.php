<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Prescriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PrescriptionsController extends Controller
{
    /**
     * عرض جميع الوصفات مع الطبيب والسجل المرتبط.
     */
    public function index()
    {
        $prescriptions = Prescriptions::with(['doctor', 'record'])->get();
        return response()->json($prescriptions);
    }

    /**
     * إنشاء وصفة طبية جديدة.
     */
    public function store(Request $request)
    {
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

    /**
     * عرض وصفة طبية واحدة مع علاقاتها.
     */
    public function show($id)
    {
        $prescription = Prescriptions::with(['doctor', 'record'])->find($id);

        if (!$prescription) {
            return response()->json(['message' => 'Prescription not found'], 404);
        }

        return response()->json($prescription);
    }

    /**
     * تعديل وصفة طبية.
     */
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
