<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Doctor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * 🔐 تسجيل مستخدم جديد (مريض - طبيب - مركز - مشرف)
     */
    public function register(Request $request)
    {
        // ✅ تحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:100',
            'email'     => 'required|string|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'user_type' => 'required|in:patient,Doctor,Center,Super_admin', // أنواع المستخدمين المسموح بها
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // ✅ إنشاء المستخدم (User)
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password), // تشفير الباسوورد
            'user_type' => $request->user_type,
        ]);

        // ✅ إذا كان المستخدم طبيب، أنشئ له سجل في جدول الأطباء
        if ($user->user_type === 'Doctor') {
            Doctor::create([
                'user_id' => $user->user_id,
                'specialty' => $request->specialty ?? '', // يمكن إضافة التخصص
                'phone' => $request->phone ?? '',
                'is_approved' => false // 🚫 الطبيب غير مفعل حتى يوافق الأدمن
            ]);
        }

        // ✅ توليد التوكن JWT لهذا المستخدم
        $token = JWTAuth::fromUser($user);

        // ✅ إرسال بيانات المستخدم والتوكن
        return response()->json([
            'user'  => $user,
            'token' => $token
        ], 201);
    }

    /**
     * 🔓 تسجيل الدخول (Login)
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // ✅ محاولة تسجيل الدخول بالتوكن
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

        // 🚫 إذا كان المستخدم طبيب ولم تتم الموافقة عليه من الأدمن → لا يسمح له بالدخول
        if ($user->user_type === 'Doctor') {
            if (!$user->doctor || !$user->doctor->is_approved) {
                return response()->json(['error' => 'Your account is pending admin approval.'], 403);
            }
        }

        // ✅ تسجيل دخول ناجح
        return response()->json(['token' => $token]);
    }

    /**
     * 🚪 تسجيل الخروج (Logout)
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * 📄 معلومات المستخدم الحالي (المسجل دخول)
     */
    public function me()
    {
        return response()->json(Auth::user());
    }
}
