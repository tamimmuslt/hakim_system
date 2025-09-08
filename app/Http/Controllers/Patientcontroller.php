<?php
namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Patientcontroller extends Controller
{
    // ---------------- INDEX ----------------
    public function index()
    {
        $user = auth('api')->user();

        if ($user->user_type === 'Super_Admin') {
            return response()->json(Patient::with('user')->get());
        }

        if ($user->user_type === 'Center' && $user->center) {
            return response()->json(
                Patient::with('user')
                    ->where('center_id', $user->center->id)
                    ->get()
            );
        }

        if ($user->user_type === 'Doctor') {
            return response()->json(
                Patient::with('user')
                    ->whereHas('appointments', function ($q) use ($user) {
                        $q->where('doctor_id', $user->id);
                    })
                    ->get()
            );
        }

        if ($user->user_type === 'Patient') {
            $profile = Patient::with('user')->where('user_id', $user->id)->first();
            return response()->json([$profile]);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // ---------------- SHOW ----------------
    public function show($id)
    {
        $user = auth('api')->user();
        $profile = Patient::with('user')->find($id);

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        // Super Admin → يقدر يشوف الكل
        if ($user->user_type === 'Super_Admin') {
            return response()->json($profile);
        }

        // Patient → يشوف نفسه فقط
        if ($user->user_type === 'Patient' && $profile->user_id === $user->id) {
            return response()->json($profile);
        }

        if ($user->user_type === 'Center' && $user->center && $profile->center_id === $user->center->id) {
            return response()->json($profile);
        }

        if ($user->user_type === 'Doctor') {
            $isMyPatient = $profile->appointments()->where('doctor_id', $user->id)->exists();
            if ($isMyPatient) {
                return response()->json($profile);
            }
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // ---------------- UPDATE ----------------
    public function update(Request $request, $id)
    {
        $user = auth('api')->user();
        $profile = Patient::find($id);

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        // المسموح لهم التحديث: المريض نفسه + الأدمن
        if (!(
            ($user->user_type === 'Super_Admin') ||
            ($user->user_type === 'Patient' && $profile->user_id === $user->id)
        )) {
            return response()->json(['message' => 'Unauthorized'], 403);
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

    // ---------------- DESTROY ----------------
    public function destroy($id)
    {
        $user = auth('api')->user();
        $profile = Patient::find($id);

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        // الحذف بس للأدمن
        if ($user->user_type !== 'Super_Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $profile->delete();
        return response()->json(['message' => 'Profile deleted']);
    }
}
