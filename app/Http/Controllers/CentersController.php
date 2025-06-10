<?php

namespace App\Http\Controllers;

use App\Models\Centers;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CentersController extends Controller
{
    
    public function index()
    {
        return response()->json(
            Centers::with(['doctors','services','promotions'])->get()
        );
    }

    public function store(Request $request)
    {
       $validator = Validator::make($request->all(), [
    'user_id'  => 'required|exists:users,user_id',
    'address'  => 'required|string',
    'phone'    => 'required|string',
    'type'     => 'required|string',
    'latitude' => 'required|numeric',
    'longitude'=> 'required|numeric',
]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
       $user = User::find($request->user_id);
        if ($user->user_type !=='Center') {
            return response()->json(['message' => 'User type must be Center only.'], 422);
    }
        
        $center = Centers::create($validator->validated());

        return response()->json($center, 201);
    }

    
    public function show($id)
    {
        $center = Centers::with(['doctors', 'services', 'promotions'])->find($id);

        if (!$center) {
            return response()->json(['message' => 'Center not found'], 404);
        }

        return response()->json($center);
    }

    public function update(Request $request, $id)
    {
        $center = Centers::find($id);

        if (!$center) {
            return response()->json(['message' => 'Center not found'], 404);
        }

     $validator = Validator::make($request->all(), [
    'user_id'  => 'required|exists:users,user_id',
    'address'  => 'required|string',
    'phone'    => 'required|string',
    'type'     => 'required|string',
    'latitude' => 'required|numeric',
    'longitude'=> 'required|numeric',
]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $center->update($validator->validated());

        return response()->json($center);
    }

   
    public function destroy($id)
    {
        $center = Centers::find($id);

        if (!$center) {
            return response()->json(['message' => 'Center not found'], 404);
        }

        $center->delete();

        return response()->json(['message' => 'Center deleted successfully']);
    }

 

public function nearbyCenters(Request $request)
{
    $validator = Validator::make($request->all(), [
        'latitude'  => 'required|numeric',
        'longitude' => 'required|numeric',
        'radius'    => 'nullable|numeric', 
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $validated = $validator->validated();
    $latitude  = $validated['latitude'];
    $longitude = $validated['longitude'];
    $radius    = $validated['radius'] ?? 10; 

    $centers = Centers::selectRaw("
        *, 
        (6371 * acos(cos(radians(?)) * cos(radians(latitude)) 
        * cos(radians(longitude) - radians(?)) 
        + sin(radians(?)) * sin(radians(latitude)))) AS distance
    ", [$latitude, $longitude, $latitude])
        ->having('distance', '<=', $radius)
        ->orderBy('distance')
        ->get();

    
    return response()->json($centers);
}

}
