<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DoctorController extends Controller
{
    // عرض جميع الأطباء
    public function index()
    {
        $doctors = Doctor::with(['user', 'availability', 'centers', 'prescriptions'])->get();
        return response()->json($doctors);
    }

    // إنشاء طبيب جديد
    public function store(Request $request)
    {
        // تحقق من البيانات
        $validator = Validator::make($request->all(), [
            'user_id'   => 'required|exists:users,user_id',
            'specialty' => 'required|string',
            'phone'     => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // تحقق أن نوع المستخدم Doctor فقط
        $user = User::find($request->user_id);
        if ($user->user_type !== 'Doctor') {
            return response()->json(['message' => 'User type must be Doctor only.'], 422);
        }

        // أنشئ الطبيب
        $doctor = Doctor::create($validator->validated());

        return response()->json($doctor, 201);
    }

    // عرض طبيب معين
    public function show($id)
    {
        $doctor = Doctor::with(['user', 'availability', 'centers', 'prescriptions'])->find($id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        return response()->json($doctor);
    }
      public function approve($id)
{
    $admin = Auth::user();
    if ($admin->user_type !== 'Super_admin') {
        return response()->json(['message' => 'Admins only'], 403);
    }

    $doctor = Doctor::find($id);
    if (!$doctor) {
        return response()->json(['message' => 'Doctor not found'], 404);
    }

    $doctor->is_approved = true;
    $doctor->save();

    return response()->json(['message' => 'Doctor approved successfully']);
}


    // تعديل بيانات الطبيب
    public function update(Request $request, $id)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'specialty' => 'sometimes|required|string',
            'phone'     => 'sometimes|required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $doctor->update($validator->validated());

        return response()->json($doctor);
    }

  

    // حذف طبيب
    public function destroy($id)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        $doctor->delete();

        return response()->json(['message' => 'Doctor deleted successfully']);
    }
}
