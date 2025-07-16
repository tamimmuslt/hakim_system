<?php

   namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;




class Patientcontroller extends Controller
{
    public function index()
    {
        $profiles = Patient::with('user')->get();
        return response()->json($profiles);
    }

    public function show($id)
    {
        $profile = Patient::with('user')->find($id);
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }
        return response()->json($profile);
    }

    public function update(Request $request, $id)
    {
        $profile = Patient::find($id);
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'birthdate' => 'nullable|date',
            'weight'    => 'nullable|integer',
            'blood_type'=> 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $profile->update($validator->validated());

        return response()->json(['message' => 'Profile updated', 'data' => $profile]);
    }

    public function destroy($id)
    {
        $profile = Patient::find($id);
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $profile->delete();
        return response()->json(['message' => 'Profile deleted']);
    }
}


