<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Appointments;

class UserController extends Controller
{
 
    // public function profile()
    // {
    //     $user = Auth::user();

    //     if (!$user) {
    //         return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
    //     }

    //     $user->loadMissing([
    //         'doctor',
    //         'center',
    //         'record', 
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'user' => $user
    //     ]);
    // }
    

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Validator;
// use App\Models\Appointments;

// class UserController extends Controller
// {
    public function profile()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
        }

        // تحميل العلاقات المرتبطة
        $user->loadMissing([
            'doctor',
            'center',
            'record',
        ]);

        // المواعيد حسب نوع المستخدم (user_type)
        if ($user->user_type === 'admin') {
            // الأدمن يشوف الكل
            $appointments = Appointments::with(['user', 'doctor', 'service'])->get();
        } elseif ($user->user_type === 'doctor') {
            // الدكتور يشوف مواعيده
            $appointments = Appointments::with(['user', 'service'])
                ->where('doctor_id', $user->doctor->doctor_id)
                ->get();
        } elseif ($user->user_type === 'clinic') {
            // المركز يشوف المواعيد اللي عنده
            $appointments = Appointments::with(['user', 'doctor', 'service'])
                ->where('clinic_id', $user->center->center_id) // لازم يكون عندك clinic_id بجدول appointments
                ->get();
        } else {
            // المريض
            $appointments = Appointments::with(['doctor', 'service'])
                ->where('user_id', $user->user_id)
                ->get();
        }

        return response()->json([
            'success' => true,
            'user' => $user,
            'appointments' => $appointments
        ]);
    }

    // update() و destroy() مثل ما عندك بدون تغيير



   
    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'sometimes|string|max:100',
            'phone'    => 'nullable|string|max:15',
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user'    => $user
        ]);
    }

   
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Incorrect password'], 401);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'Account deleted successfully']);
    }
}
