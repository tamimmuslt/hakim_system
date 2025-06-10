<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceBookings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServicesbookingController extends Controller
{
    /**
     * عرض جميع الحجوزات مع بيانات المستخدم والخدمة.
     */
    public function index()
    {
        $bookings = ServiceBookings::with(['user', 'service'])->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * إنشاء حجز جديد.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'          => 'required|exists:users,user_id',
            'service_id'       => 'required|exists:services,service_id',
            'booking_datetime' => 'required|date|after_or_equal:now',
            'status'           => 'required|in:pending,confirmed,completed,cancelled',
            'notes'            => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $booking = ServiceBookings::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Service booking created successfully',
            'data'    => $booking
        ], 201);
    }

    /**
     * عرض حجز معين.
     */
    public function show($id)
    {
        $booking = ServiceBookings::with(['user', 'service'])->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }

    /**
     * تحديث حجز.
     */
    public function update(Request $request, $id)
    {
        $booking = ServiceBookings::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'booking_datetime' => 'sometimes|date|after_or_equal:now',
            'status'           => 'sometimes|in:pending,confirmed,completed,cancelled',
            'notes'            => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $booking->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'data'    => $booking
        ]);
    }

    /**
     * حذف حجز.
     */
    public function destroy($id)
    {
        $booking = ServiceBookings::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        $booking->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service booking deleted successfully'
        ]);
    }
}
