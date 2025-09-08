<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use App\Models\User;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Validator;
// use Tymon\JWTAuth\Facades\JWTAuth;
// use Illuminate\Support\Facades\Mail;
// use App\Mail\PasswordResetMail;
// use App\Notifications\WelcomeNotification;
// // use App\Notifications\ResetPasswordNotification;
// class AuthController extends Controller
// {

// public function register(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'name'      => 'required|string|max:255',
//         'email'     => [
//             'required',
//             'string',
//             'email',
//             'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
//             'unique:users,email',
//         ],
//         'password'  => 'required|string|min:6',
//         'user_type' => 'required|in:patient',
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'success' => false,
//             'errors'  => $validator->errors()
//         ], 200);
//     }

//     $verificationCode = rand(100000, 999999);

//     $user = User::create([
//         'name'                    => $request->name,
//         'email'                   => $request->email,
//         'password'                => Hash::make($request->password),
//         'user_type'               => $request->user_type,
//         'email_verification_code' => $verificationCode,
//         'is_email_verified'       => false,
//     ]);

//     if ($user->user_type === 'patient') {
//         $user->patient()->create([
//             'birthdate'  => $request->birthdate ?? null,
//             'weight'     => $request->weight ?? null,
//             'height'     => $request->height ?? null,
//             'blood_type' => $request->blood_type ?? null,
//         ]);
//     }

//     $user->notify(new WelcomeNotification($user, $verificationCode));

//     $token = JWTAuth::fromUser($user);

//     return response()->json([
//         'success' => true,
//         'message' => 'User registered successfully. Verification code sent to your email.',
//         'token'   => $token,
//     ], 201);
// }

//     public function sendEmailVerificationCode(Request $request)
//     {
//         $user = JWTAuth::user();

//         if ($user->is_email_verified) {
//             return response()->json(['message' => 'Email already verified.'], 400);
//         }

//         $verificationCode = rand(100000, 999999);
//         $user->email_verification_code = $verificationCode;
//         $user->save();
// $user->notify(new WelcomeNotification($user, $verificationCode));
        

//         return response()->json(['message' => 'Verification code resent to your email.']);
//     }

//     public function verifyEmailCode(Request $request)
//     {
//         $request->validate([
//             'code' => 'required|string',
//         ]);

//         $user = JWTAuth::user();

//         if ($user->is_email_verified) {
//             return response()->json(['message' => 'Email already verified.']);
//         }

//        if ((string)$user->email_verification_code !== (string)$request->code) {
//     return response()->json(['error' => 'Invalid verification code.'], 400);
// }

//         $user->is_email_verified = true;
//         $user->email_verification_code = null;
//         $user->save();
// $user->notify(new WelcomeNotification($user));
//         return response()->json(['message' => 'Email verified successfully.']);
//     }

//   public function login(Request $request)
// {
//     $credentials = $request->only('email', 'password');

//     if (!$token = JWTAuth::attempt($credentials)) {
//         return response()->json(['error' => 'Unauthorized'], 401);
//     }

//     try {
//         $user = JWTAuth::setToken($token)->toUser();
//     } catch (\Exception $e) {
//         return response()->json(['error' => 'Could not retrieve user.'], 500);
//     }

//     if (!$user) {
//         return response()->json(['error' => 'User not found.'], 404);
//     }

// if ($user->user_type === 'patient' && !$user->is_email_verified) {
//     return response()->json(['message' => 'Email not verified. Please verify your email first.'], 403);
// }

//     // if ($user->user_type === 'Doctor' && (!$user->doctor || !$user->doctor->is_approved)) {
//     //     return response()->json(['error' => 'Your account is pending admin approval.'], 403);
//     // }

//     return response()->json([
//         'token' => $token,
//         'user' => $user,
//     ]);
// }


// public function resetPassword(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'email'    => 'required|email|exists:users,email',
//         'code'     => 'required|string',
//         'password' => 'required|string|min:6|confirmed',
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'success' => false,
//             'errors'  => $validator->errors()
//         ], 200);
//     }

//     $user = User::where('email', $request->email)->first();

//     if ($user->password_reset_code !== $request->code) {
//         return response()->json(['success' => false, 'message' => 'Invalid reset code.'], 400);
//     }

//     if (now()->greaterThan($user->password_reset_expires_at)) {
//         return response()->json(['success' => false, 'message' => 'Reset code expired.'], 400);
//     }

//     // تحديث الباسوورد
//     $user->password = Hash::make($request->password);
//     $user->password_reset_code = null;
//     $user->password_reset_expires_at = null;
//     $user->save();

//     // إرسال إيميل تأكيد للمستخدم
//     Mail::to($user->email)->send(new PasswordResetMail($user));

//     return response()->json([
//         'success' => true,
//         'message' => 'Password has been reset successfully.'
//     ], 200);
// }public function sendPasswordResetCode(Request $request)
// {
//     // التحقق من البريد
//     $validator = Validator::make($request->all(), [
//         'email' => 'required|email|exists:users,email',
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'success' => false,
//             'errors'  => $validator->errors()
//         ], 200); 
//     }

//     $user = User::where('email', $request->email)->first();

//     $resetCode = rand(100000, 999999);
//     $user->password_reset_code = $resetCode;
//     $user->password_reset_expires_at = now()->addMinutes(15);
//     $user->save();

//         Mail::to($user->email)->send(new PasswordResetMail($user));

//     return response()->json([
//         'success' => true,
//         'message' => 'Password reset code sent to your email.'
//     ], 200);
// }

//     public function logout()
//     {
//         JWTAuth::invalidate(JWTAuth::getToken());
//         return response()->json(['message' => 'Logged out successfully']);
//     }

//     public function me()
//     {
//         return response()->json(JWTAuth::user());
//     }
// }

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use App\Notifications\WelcomeNotification;
use App\Mail\PasswordResetMail;
use App\Notifications\PasswordResetCode;

class AuthController extends Controller
{
    // تسجيل مستخدم جديد
    public function register(Request $request)
    {
     $validator = Validator::make($request->all(), [
    'name'      => 'required|string|max:255',
    'email'     => [
        'required',
        'string',
        'email',
        'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
        'unique:users,email',
    ],
    'password'  => 'required|string|min:6|confirmed',
    'user_type' => 'required|in:patient',
    'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 200);
        }

        $verificationCode = rand(100000, 999999);

        $user = User::create([
            'name'                    => $request->name,
            'email'                   => $request->email,
            'password'                => Hash::make($request->password),
            'user_type'               => $request->user_type,
            'email_verification_code' => $verificationCode,
            'is_email_verified'       => false,
        ]);

        if ($user->user_type === 'patient') {
            $user->patient()->create([
                'birthdate'  => $request->birthdate ?? null,
                'weight'     => $request->weight ?? null,
                'height'     => $request->height ?? null,
                'blood_type' => $request->blood_type ?? null,
            ]);
        }

        $user->notify(new WelcomeNotification($user, $verificationCode));

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully. Verification code sent to your email.',
            'token'   => $token,
        ], 201);
    }

    // إرسال كود التحقق من البريد
    public function sendEmailVerificationCode(Request $request)
    {
        $user = JWTAuth::user();

        if ($user->is_email_verified) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        $verificationCode = rand(100000, 999999);
        $user->email_verification_code = $verificationCode;
        $user->save();

        $user->notify(new WelcomeNotification($user, $verificationCode));

        return response()->json(['message' => 'Verification code resent to your email.']);
    }

    public function verifyEmailCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = JWTAuth::user();

        if ($user->is_email_verified) {
            return response()->json(['message' => 'Email already verified.']);
        }

        if ((string)$user->email_verification_code !== (string)$request->code) {
            return response()->json(['error' => 'Invalid verification code.'], 400);
        }

        $user->is_email_verified = true;
        $user->email_verification_code = null;
        $user->save();

        $user->notify(new WelcomeNotification($user));

        return response()->json(['message' => 'Email verified successfully.']);
    }

    // تسجيل الدخول
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $user = JWTAuth::setToken($token)->toUser();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not retrieve user.'], 500);
        }

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        if ($user->user_type === 'patient' && !$user->is_email_verified) {
            return response()->json(['message' => 'Email not verified. Please verify your email first.'], 403);
        }

        return response()->json([
            'token' => $token,
            'user'  => $user,
        ]);
    }

    // إرسال كود إعادة تعيين كلمة المرور

public function sendPasswordResetCode(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 200);
    }

    $user = User::where('email', $request->email)->first();
    $resetCode = rand(100000, 999999);

    $user->password_reset_code = $resetCode;
    $user->password_reset_expires_at = now()->addMinutes(15);
    $user->save();

    // إرسال الإشعار
    $user->notify(new PasswordResetCode($resetCode));

    return response()->json([
        'success' => true,
        'message' => 'Password reset code sent successfully via notification.'
    ], 200);
}

public function resetPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email'    => 'required|email|exists:users,email',
        'code'     => 'required|string',
        'password' => 'required|string|min:6|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 200);
    }

    $user = User::where('email', $request->email)->first();

    if ($user->password_reset_code !== $request->code) {
        return response()->json(['success' => false, 'message' => 'Invalid reset code.'], 400);
    }

    if (now()->greaterThan($user->password_reset_expires_at)) {
        return response()->json(['success' => false, 'message' => 'Reset code expired.'], 400);
    }

    $user->password = Hash::make($request->password);
    $user->password_reset_code = null;
    $user->password_reset_expires_at = null;
    $user->save();

    // إرسال الإشعار أو البريد
    $user->notify(new PasswordResetCode($request->code)); // Notification
    // Mail::to($user->email)->send(new PasswordResetMail($user, $request->code)); // Mail فقط

    return response()->json([
        'success' => true,
        'message' => 'Password has been reset successfully.'
    ], 200);
}

    // تسجيل الخروج
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Logged out successfully']);
    }

    // بيانات المستخدم الحالي
    public function me()
    {
        return response()->json(JWTAuth::user());
    }
    
}