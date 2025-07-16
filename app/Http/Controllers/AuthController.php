<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use App\Notifications\WelcomeNotification;

class AuthController extends Controller
{

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
        'password'  => 'required|string|min:6',
        'user_type' => 'required|in:patient',
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

    // if ($user->user_type === 'Doctor' && (!$user->doctor || !$user->doctor->is_approved)) {
    //     return response()->json(['error' => 'Your account is pending admin approval.'], 403);
    // }

    return response()->json([
        'token' => $token,
        'user' => $user,
    ]);
}

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me()
    {
        return response()->json(JWTAuth::user());
    }
}
