<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
  use App\Notifications\WelcomeNotification;
  use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    public function index()
    {
        return response()->json(Doctor::with(['user', 'availability', 'centers', 'prescriptions'])->get());
    }

   public function store(Request $request)
{
    $centerUser = Auth::user();
    if (!$centerUser || $centerUser->user_type !== 'Center' || !$centerUser->center) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $validator = Validator::make($request->all(), [
        'name'      => 'required|string|max:255',
        'email'     => 'required|email|unique:users,email',
        'password'  => 'required|string|min:6',
        'specialty' => 'required|string',
        'phone'     => 'required|string|max:20',
        'service_id' => 'required|exists:Services,service_id', // <== أضف هذا
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user = User::create([
        'name'      => $request->name,
        'email'     => $request->email,
        'password'  => Hash::make($request->password),
        'user_type' => 'Doctor',
        'is_email_verified' => false,
    ]);

  $doctor = Doctor::create([
    'user_id'   => $user->user_id,
    'specialty' => $request->specialty,
    'phone'     => $request->phone,
    'service_id'=> $request->service_id,
    'bio'       => $request->bio ?? null, 
    'is_approved' => false,
]);


    $centerUser->center->doctors()->syncWithoutDetaching($doctor->doctor_id);

    $user->notify(new WelcomeNotification($user));

    return response()->json(['doctor' => $doctor, 'user' => $user], 201);
}

    public function approve($id)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'Super_Admin') return response()->json(['message' => 'Admins only'], 403);

        $doctor = Doctor::find($id);
        if (!$doctor) return response()->json(['message' => 'Doctor not found'], 404);

        $doctor->is_approved = true;
        $doctor->save();

        return response()->json(['message' => 'Doctor approved successfully']);
    }

public function update(Request $request, $doctor_id)
{
    $user = auth('api')->user();

    // جلب الطبيب مع تحديد الجدول لتجنب ambiguity
    $doctor = Doctor::select('doctors.*')->find($doctor_id);
    if (!$doctor) {
        return response()->json(['message' => 'Doctor not found'], 404);
    }

    // التحقق من الصلاحية: المركز المرتبط أو الطبيب نفسه
    if ($user->user_type === 'Doctor') {
        // حدد جدول doctors هنا
        if ($user->doctor->doctor_id !== $doctor_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    } elseif ($user->user_type === 'Center') {
        // حدد جدول pivot عند التحقق من العلاقة
        $centerDoctorIds = $user->center->doctors()->pluck('doctors.doctor_id')->toArray();
        if (!in_array($doctor_id, $centerDoctorIds)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    } else {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // قواعد التحقق
  $validator = Validator::make($request->all(), [
    'specialty'  => 'sometimes|string|max:255',
    'phone'      => 'sometimes|string|max:20',
    'service_id' => 'sometimes|exists:services,service_id',
    'bio'        => 'sometimes|string', // 👈 السماح بتحديث الوصف
]);

// $doctor->update($validator->validated());

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // تحديث البيانات
    $doctor->update($validator->validated());

    return response()->json([
        'success' => true,
        'message' => 'Doctor updated successfully',
        'data' => $doctor->load('user', 'centers', 'service')
    ]);
}


    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->user_type !== 'Center' || !$user->center) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $doctor = Doctor::find($id);
        if (!$doctor) return response()->json(['message' => 'Doctor not found'], 404);

        if (!$doctor->centers->contains($user->center->center_id)) {
            return response()->json(['message' => 'This doctor does not belong to your center'], 403);
        }

        $doctor->delete();

        return response()->json(['message' => 'Doctor deleted successfully']);
    }
}
