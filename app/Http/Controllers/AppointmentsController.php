<?php

// namespace App\Http\Controllers;

// use App\Models\Appointments;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Validator;
// use App\Models\Notifications;
// use Carbon\Carbon;

// class AppointmentsController extends Controller
// {
//     // public function index()
//     // {
//     //     $appointments = Appointments::with(['user', 'doctor','service'])->get();
//     //     return response()->json($appointments);
//     // }
// public function index(Request $request)
// {
//     $user = $request->user();

//     if ($user->user_type !== 'admin') {
//         return response()->json(['message' => 'غير مسموح'], 403);
//     }

//     $appointments = Appointments::with(['user', 'doctor','service'])->get();
//     return response()->json($appointments);
// }
// // use App\Models\Appointments;
// // use App\Models\Notification;
// // use Carbon\Carbon;
// // use Illuminate\Support\Facades\Validator;

// public function store(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'user_id' => 'required|exists:users,user_id',
//         'doctor_id' => 'required|exists:doctors,doctor_id',
//         'service_id' => 'required|exists:services,service_id',
//         'appointment_datetime' => 'required|date',
//         'status' => 'required|in:scheduled,completed,cancelled,no_show',
//         'notes' => 'nullable|string',
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['errors' => $validator->errors()], 422);
//     }

//     // إنشاء الموعد
//     $appointment = Appointments::create($request->all());

//     // حساب وقت التذكير قبل ساعتين
//     $reminderTime = Carbon::parse($appointment->appointment_datetime)->subHours(2);

//     // إنشاء إشعار للمريض
//     Notifications::create([
//         'user_id' => $appointment->user_id,
//         'message_text' => 'لديك موعد بعد ساعتين مع الطبيب: ' . $appointment->doctor->name,
//         'type' => 'reminder',
//         'scheduled_at' => $reminderTime,
//     ]);

//     // إنشاء إشعار للدكتور
//     Notifications::create([
//         'user_id' => $appointment->doctor_id,
//         'message_text' => 'لديك موعد بعد ساعتين مع المريض: ' . $appointment->user->name,
//         'type' => 'reminder',
//         'scheduled_at' => $reminderTime,
//     ]);

//     return response()->json($appointment, 201);
// }

//     public function show($id)
//     {
//         $appointment = Appointments::with(['user', 'doctor','service'])->find($id);

//         if (!$appointment) {
//             return response()->json(['message' => 'Appointment not found'], 404);
//         }

//         return response()->json($appointment);
//     }

  
//     public function update(Request $request, $id)
//     {
//         $appointment = Appointments::find($id);

//         if (!$appointment) {
//             return response()->json(['message' => 'Appointment not found'], 404);
//         }

//         $validator = Validator::make($request->all(), [
//             'user_id' => 'sometimes|exists:users,user_id',
//             'doctor_id' => 'sometimes|exists:doctors,doctor_id',
//             'service_id' => 'sometimes|exists:services,service_id',
//             'appointment_datetime' => 'sometimes|date',
//             'status' => 'sometimes|in:scheduled,completed,cancelled,no_show',
//             'notes' => 'nullable|string',
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['errors' => $validator->errors()], 422);
//         }

//         $appointment->update($request->all());
//         return response()->json($appointment);
//     }

   
//     public function destroy($id)
//     {
//         $appointment = Appointments::find($id);

//         if (!$appointment) {
//             return response()->json(['message' => 'Appointment not found'], 404);
//         }

//         $appointment->delete();
//         return response()->json(['message' => 'Appointment deleted']);
//     }
// }


namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Models\Notifications;
use App\Jobs\SendAppointmentReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppointmentsController extends Controller
{
    /**
     * ✅ عرض جميع المواعيد المتاحة فقط
     */
    public function index()
    {
        $appointments = Appointments::with(['doctor', 'service'])
            ->where('status', 'available')
            ->get();

        return response()->json($appointments);
    }

    /**
     * ✅ إنشاء موعد جديد مع التحقق + إشعارات + تذكير
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'              => 'required|exists:users,user_id',
            'doctor_id'            => 'required|exists:doctors,doctor_id',
            'service_id'           => 'required|exists:services,service_id',
            'appointment_datetime' => 'required|date',
            'notes'                => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // ✅ التحقق إذا الموعد محجوز مسبقًا
        $exists = Appointments::where('doctor_id', $request->doctor_id)
            ->where('appointment_datetime', $request->appointment_datetime)
            ->where('status', 'scheduled')
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'الموعد غير متاح'], 400);
        }

        // ✅ إنشاء الموعد
        $appointment = Appointments::create([
            'user_id'              => $request->user_id,
            'doctor_id'            => $request->doctor_id,
            'service_id'           => $request->service_id,
            'appointment_datetime' => $request->appointment_datetime,
            'status'               => 'scheduled',
            'notes'                => $request->notes,
        ]);

        // ✅ إشعار تأكيد للمريض
        Notifications::create([
            'user_id'      => $appointment->user_id,
            'message_text' => "تم حجز موعدك مع الدكتور {$appointment->doctor->name} بتاريخ " .
                              Carbon::parse($appointment->appointment_datetime)->format('Y-m-d H:i') . ".",
            'is_read'      => false,
            'type'         => 'confirmation',
        ]);

        // ✅ جدولة تذكير قبل ساعتين
        $reminderTime = Carbon::parse($appointment->appointment_datetime)->subHours(2);
        SendAppointmentReminder::dispatch($appointment)->delay($reminderTime);

        return response()->json([
            'message'     => 'تم الحجز بنجاح',
            'appointment' => $appointment
        ], 201);
    }

    /**
     * ✅ عرض موعد واحد
     */
    public function show($id)
    {
        $appointment = Appointments::with(['user', 'doctor', 'service'])->find($id);

        if (!$appointment) {
            return response()->json(['message' => 'الموعد غير موجود'], 404);
        }

        return response()->json($appointment);
    }

    /**
     * ✅ تعديل موعد
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointments::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'الموعد غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'doctor_id'            => 'sometimes|exists:doctors,doctor_id',
            'service_id'           => 'sometimes|exists:services,service_id',
            'appointment_datetime' => 'sometimes|date',
            'status'               => 'sometimes|in:available,scheduled,completed,cancelled,no_show',
            'notes'                => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $appointment->update($request->all());

        return response()->json([
            'message'     => 'تم تعديل الموعد بنجاح',
            'appointment' => $appointment
        ]);
    }

    /**
     * ✅ حذف موعد
     */
    public function destroy($id)
    {
        $appointment = Appointments::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'الموعد غير موجود'], 404);
        }

        $appointment->delete();

        return response()->json(['message' => 'تم حذف الموعد بنجاح']);
    }
}
