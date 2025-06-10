<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * عرض الملف الشخصي
     */
    public function profile()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
        }

        // تحميل العلاقة حسب نوع المستخدم
switch ($user->user_type) {
    case 'Center':
        $user->load('Center');
        break;

    case 'Doctor':
        $user->load('Doctor');
        break;
}

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * تعديل بيانات المستخدم
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name'       => 'sometimes|string|max:100',
            'phone'      => 'nullable|string|max:15',
            'birthdate'  => 'nullable|date',
            'weight'     => 'nullable|integer',
            'blood_type' => 'nullable|string|max:20',
            'password'   => 'nullable|string|min:6',
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

    /**
     * حذف حساب المستخدم (بعد التحقق من كلمة المرور)
     */
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
