<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Centers;
use App\Models\Promotions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
   
    public function doctors()
    {
        if (auth('api')->user()->user_type !== 'Super_Admin')
            return response()->json(['message' => 'Unauthorized'], 403);

        return response()->json(Doctor::with('user')->get());
    }

    public function approveDoctor($id)
    {
        if (auth('api')->user()->user_type !== 'Super_Admin')
            return response()->json(['message' => 'Unauthorized'], 403);

        $doctor = Doctor::find($id);
        if (!$doctor)
            return response()->json(['message' => 'Doctor not found'], 404);

        $doctor->is_approved = true;
        $doctor->save();

        return response()->json(['message' => 'Doctor approved successfully']);
    }

    public function users()
    {
        if (auth('api')->user()->user_type !== 'Super_Admin')
            return response()->json(['message' => 'Unauthorized'], 403);

        return response()->json(User::all());
    }

    public function deleteUser($id)
    {
        if (auth('api')->user()->user_type !== 'Super_Admin')
            return response()->json(['message' => 'Unauthorized'], 403);

        $user = User::find($id);
        if (!$user)
            return response()->json(['message' => 'User not found'], 404);

        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    public function approveCenter($id)
    {
        if (auth('api')->user()->user_type !== 'Super_Admin')
            return response()->json(['message' => 'Unauthorized'], 403);

        $center = Centers::find($id);
        if (!$center)
            return response()->json(['message' => 'Center not found'], 404);

        $center->is_approved = true;
        $center->save();

        return response()->json(['message' => 'Center approved successfully']);
    }

    public function deleteCenter($id)
    {
        if (auth('api')->user()->user_type !== 'Super_Admin')
            return response()->json(['message' => 'Unauthorized'], 403);

        $center = Centers::find($id);
        if (!$center)
            return response()->json(['message' => 'Center not found'], 404);

        $center->delete();
        return response()->json(['message' => 'Center deleted']);
    }

    //  إضافة أدمن جديد
    public function addAdmin(Request $request)
    {
        if (auth('api')->user()->user_type !== 'Super_Admin')
            return response()->json(['message' => 'Unauthorized'], 403);

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => [
                'required',
                'string',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
                'unique:users,email',
            ],
            'password' => 'required|string|min:6',
        ], [
            'email.regex' => 'البريد يجب أن يكون @gmail.com فقط',
        ]);

        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'user_type' => 'Super_Admin',
        ]);

      
        return response()->json(['message' => 'Admin added successfully', 'admin' => $user]);
    }

   public function addCenter(Request $request)
{
    if (auth('api')->user()->user_type !== 'Super_Admin')
        return response()->json(['message' => 'Unauthorized'], 403);

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
        'address'  => 'required|string',
        'phone'     => 'required|string|max:20',
        'type'      => 'required|string',
        'latitude'  => 'required|numeric',
        'longitude' => 'required|numeric',
    ], [
        'email.regex' => 'البريد يجب أن يكون @gmail.com فقط',
    ]);

    if ($validator->fails())
        return response()->json(['errors' => $validator->errors()], 422);

    $user = User::create([
        'name'      => $request->name,
        'email'     => $request->email,
        'password'  => Hash::make($request->password),
        'user_type' => 'Center',
    ]);

    $user->center()->create([
        'address'     => $request->address,
        'phone'        => $request->phone,
        'type'         => $request->type,
        'latitude'     => $request->latitude,
        'longitude'    => $request->longitude,
        'is_approved'  => false,  // يبدأ غير مفعل
    ]);

    return response()->json([
        'message' => 'Center added successfully',
        'center'  => $user->load('center')
    ]);
}


   

// public function addPromotion(Request $request)
// {
//     if (auth('api')->user()->user_type !== 'Center') {
//         return response()->json(['message' => 'Unauthorized'], 403);
//     }

//     $validator = Validator::make($request->all(), [
//         'center_id'           => 'required|exists:centers,center_id',
//         'title'               => 'required|string|max:150',
//         'description'         => 'required|string',
//         'start_date'          => 'required|date',
//         'end_date'            => 'required|date|after_or_equal:start_date',
//         'discount_percent'    => 'required|numeric|min:0|max:100',
//         'price_after_discount'=> 'required|numeric|min:0',
//         'is_active'           => 'sometimes|boolean',
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['errors' => $validator->errors()], 422);
//     }

//     $promotion = Promotions::create($validator->validated());

//     return response()->json(['message' => 'Promotion created', 'promotion' => $promotion]);
// }

public function approvePromotion($id)
{
    if (auth('api')->user()->user_type !== 'Super_Admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $promotion = Promotions::find($id);
    if (!$promotion) {
        return response()->json(['message' => 'Promotion not found'], 404);
    }

    $promotion->is_active = true;
    $promotion->save();

    return response()->json(['message' => 'Promotion approved successfully']);
}

}
