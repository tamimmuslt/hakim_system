<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MedicalRecordsController extends Controller
{
    
    public function index()
    {
        $records = MedicalRecords::with([
            'user',
            'appointment',
            'prescriptions',
            'radiologyImages',
            'labTests'
        ])->get();

        return response()->json($records);
    }

   
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'         => 'required|exists:users,user_id',
            'appointment_id'  => 'required|exists:appointments,appointment_id',
            'diagnosis'       => 'nullable|string',
            'treatment_plan'  => 'nullable|string',
            'progress_notes'  => 'nullable|string',
            'start_date'      => 'nullable|date',
            'end_date'        => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $record = MedicalRecords::create($validator->validated());

        return response()->json($record, 201);
    }


    public function show($id)
    {
        $record = MedicalRecords::with([
            'user',
            'appointment',
            'prescriptions',
            'radiologyImages',
            'labTests'
        ])->find($id);

        if (!$record) {
            return response()->json(['message' => 'Medical record not found'], 404);
        }

        return response()->json($record);
    }

  
    public function update(Request $request, $id)
    {
        $record = MedicalRecords::find($id);

        if (!$record) {
            return response()->json(['message' => 'Medical record not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'diagnosis'       => 'nullable|string',
            'treatment_plan'  => 'nullable|string',
            'progress_notes'  => 'nullable|string',
            'start_date'      => 'nullable|date',
            'end_date'        => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $record->update($validator->validated());

        return response()->json($record);
    }

    
    public function destroy($id)
    {
        $record = MedicalRecords::find($id);

        if (!$record) {
            return response()->json(['message' => 'Medical record not found'], 404);
        }

        $record->delete();

        return response()->json(['message' => 'Medical record deleted successfully']);
    }
}
