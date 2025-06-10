<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\LabTests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class LabTestsController extends Controller
{
  
    public function index()
    {
        $labTests = LabTests::with(['record', 'uploader'])->get();

        return response()->json([
            'success' => true,
            'data' => $labTests
        ], 200);
    }

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'record_id'   => 'required|exists:medical_records,record_id',
            'uploaded_by' => 'required|exists:users,user_id',
            'test_name'   => 'required|string|max:255',
            'result_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'test_date'   => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
$user = Auth::user();

if ($user->user_type !== 'Doctor' || !$user->doctor || !$user->doctor->is_approved) {
    return response()->json(['message' => 'Only approved doctors can upload data'], 403);
}
        $filePath = null;
        if ($request->hasFile('result_file')) {
           
            $filePath = $request->file('result_file')->store('lab_tests', 'public');
        }

       
        $labTest = LabTests::create([
            'record_id'   => $validator->validated()['record_id'],
            'uploaded_by' => $validator->validated()['uploaded_by'],
            'test_name'   => $validator->validated()['test_name'],
            'result'      => $filePath ? Storage::url($filePath) : null,
            'test_date'   => $validator->validated()['test_date'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lab test created successfully',
            'data' => $labTest
        ], 201);
    }

  
    public function show($id)
    {
        $labTest = LabTests::with(['record', 'uploader'])->find($id);

        if (!$labTest) {
            return response()->json([
                'success' => false,
                'message' => 'Lab test not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $labTest
        ], 200);
    }

   
    public function update(Request $request, $id)
    {
        $labTest = LabTests::find($id);

        if (!$labTest) {
            return response()->json([
                'success' => false,
                'message' => 'Lab test not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'record_id'   => 'sometimes|required|exists:medical_records,record_id',
            'uploaded_by' => 'sometimes|required|exists:users,user_id',
            'test_name'   => 'sometimes|required|string|max:255',
            'result_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'test_date'   => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('result_file')) {
            if ($labTest->result) {
                $oldPath = str_replace('/storage/', '', $labTest->result);
                Storage::disk('public')->delete($oldPath);
            }

            $filePath = $request->file('result_file')->store('lab_tests', 'public');
            $data['result'] = Storage::url($filePath);
        }

        $labTest->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Lab test updated successfully',
            'data' => $labTest
        ], 200);
    }

   
    public function destroy($id)
    {
        $labTest = LabTests::find($id);

        if (!$labTest) {
            return response()->json([
                'success' => false,
                'message' => 'Lab test not found'
            ], 404);
        }

        if ($labTest->result) {
            $oldPath = str_replace('/storage/', '', $labTest->result);
            Storage::disk('public')->delete($oldPath);
        }

        $labTest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lab test deleted successfully'
        ], 200);
    }
}
