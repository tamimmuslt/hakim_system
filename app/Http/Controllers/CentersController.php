<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Centers;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CentersController extends Controller
{
    public function index()
    {
        $centers = Centers::with(['doctors', 'services', 'promotions'])
            ->where('is_approved', true)  // عرض المراكز المعتمدة فقط
            ->get();

        return response()->json($centers);
    }

    public function update(Request $request, $id)
    {
      

        $center = Centers::find($id);
        if (!$center) return response()->json(['message' => 'Center not found'], 404);

        $validator = Validator::make($request->all(), [
            'address'   => 'sometimes|required|string',
            'phone'     => 'sometimes|required|string|max:20',
            'type'      => 'sometimes|required|string',
            'latitude'  => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
            'is_approved' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $center->update($validator->validated());

        return response()->json(['message' => 'Center updated successfully', 'center' => $center]);
    }

    public function destroy($id)
    {
        $admin = Auth::user();
        if (!$admin || $admin->user_type !== 'Super_Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $center = Centers::find($id);
        if (!$center) return response()->json(['message' => 'Center not found'], 404);

        $center->delete();

        return response()->json(['message' => 'Center deleted successfully']);
    }

    // ربط طبيب بمركز (صلاحية المركز فقط)
    // public function addDoctor(Request $request, $center_id)
    // {
    //     $user = Auth::user();

    //     // تأكد أن المستخدم من نوع Center وله علاقة بالمركز
    //     if (!$user || $user->user_type !== 'Center' || !$user->center || $user->center->center_id != $center_id) {
    //         return response()->json(['message' => 'Unauthorized'], 403);
    //     }

    //     $center = Centers::find($center_id);
    //     if (!$center) return response()->json(['message' => 'Center not found'], 404);

    //     $validator = Validator::make($request->all(), [
    //         'doctor_id' => 'required|exists:doctors,doctor_id',
    //     ]);

    //     if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

    //     $center->doctors()->syncWithoutDetaching($request->doctor_id);

    //     return response()->json(['message' => 'Doctor linked to center successfully']);
    // }
    public function createDoctor(Request $request, $center_id)
{
    $user = Auth::user();

    if (!$user || $user->user_type !== 'Center' || !$user->center || $user->center->center_id != $center_id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $validator = Validator::make($request->all(), [
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|string|min:6',
        'specialty' => 'required|string',
        'phone'=>'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $userData = [
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'user_type' => 'Doctor',
        'is_approved' => false,
    ];
    $newUser = User::create($userData);

    $doctor = new Doctor([
        'user_id' => $newUser->user_id,
        'specialty' => $request->specialty,
          'phone'     => $request->phone,
    ]);
    $doctor->save();

    $center = Centers::find($center_id);
    $center->doctors()->attach($doctor->doctor_id);

    return response()->json([
        'message' => 'Doctor created and linked successfully',
        'login' => [
            'email' => $newUser->email,
            'password' => $request->password 
        ]
    ]);
}


    public function approve($id)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'Super_Admin') return response()->json(['message' => 'Admins only'], 403);

        $center = Centers::find($id);
        if (!$center) return response()->json(['message' => 'Center not found'], 404);

        $center->is_approved = true;
        $center->save();

        return response()->json(['message' => 'Center approved successfully']);
    }
}