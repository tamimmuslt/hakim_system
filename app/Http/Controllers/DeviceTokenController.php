<?php
// app/Http/Controllers/DeviceTokenController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Auth;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string',
        ]);

        $user = auth::user();

        DeviceToken::updateOrCreate(
            ['user_id' => $user->user_id, 'token' => $request->device_token],
            ['enabled' => true]
        );

        return response()->json(['message' => 'تم تخزين التوكن بنجاح']);
    }
}
