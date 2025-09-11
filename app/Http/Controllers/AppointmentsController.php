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
use App\Models\DoctorAvailability;
use App\Models\Doctor;
use App\Models\Notifications;
use App\Jobs\SendAppointmentReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentsController extends Controller
{
    
     // ✅ عرض جميع المواعيد المتاحة فقط

public function index(Request $request)
{
    $doctor_id = $request->query('doctor_id');
    $date = $request->query('date', Carbon::today()->toDateString()); // افتراضي اليوم

    if (!$doctor_id) {
        return response()->json(['message' => 'doctor_id is required'], 422);
    }

    // تحقق من أن الطبيب موافق عليه
    $doctor = Doctor::find($doctor_id);
    if (!$doctor || $doctor->is_approved != 1) {
        return response()->json([
            'doctor_id' => $doctor_id,
            'date' => $date,
            'slots' => [],
            'message' => 'Doctor is not approved or does not exist'
        ]);
    }

    // مصفوفة لترجمة رقم اليوم إلى نص
    $days = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
    $dayOfWeekNumber = Carbon::parse($date)->dayOfWeek; // 0=Sunday, 1=Monday, ..., 6=Saturday
    $dayOfWeek = $days[$dayOfWeekNumber];

    // جلب توافر الطبيب لذلك اليوم
    $availability = DoctorAvailability::where('doctor_id', $doctor_id)
        ->where('day_of_week', $dayOfWeek)
        ->first();

    if (!$availability) {
        return response()->json([
            'doctor_id' => $doctor_id,
            'date' => $date,
            'slots' => []
        ]);
    }

    $start = Carbon::parse($availability->start_time);
    $end   = Carbon::parse($availability->end_time);
    $slots = [];

    while ($start < $end) {
        $slotStart = $start->copy();
        $slotEnd   = $start->copy()->addMinutes(30);

        // تحقق إذا هذا الموعد محجوز مسبقًا داخل فترة الـ slot
        $exists = Appointments::where('doctor_id', $doctor_id)
            ->where('status', 'scheduled')
            ->whereBetween('appointment_datetime', [
                $date . ' ' . $slotStart->format('H:i:s'),
                $date . ' ' . $slotEnd->format('H:i:s')
            ])
            ->exists();

        if (!$exists) {
            $slots[] = $slotStart->format('H:i');
        }

        $start->addMinutes(30); // الانتقال للـ slot التالي
    }

    return response()->json([
        'doctor_id' => $doctor_id,
        'date' => $date,
        'slots' => $slots
    ]);
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

public function availableSlots(Request $request, $doctorId)
{
    $slotMinutes = (int) $request->query('slot', 30); // مدة كل سلوت بالدقائق
    $startDateStr = $request->query('start_date', now()->toDateString());
    $endDateStr = $request->query('end_date', now()->addDays(14)->toDateString());

    $start = Carbon::parse($startDateStr)->startOfDay();
    $end = Carbon::parse($endDateStr)->endOfDay();

    // جدول أوقات الطبيب
    $availabilities = DoctorAvailability::where('doctor_id', $doctorId)->get();

    // الحجوزات الموجودة مسبقًا
    $booked = Appointments::where('doctor_id', $doctorId)
        ->where('status', 'scheduled')
        ->whereBetween('appointment_datetime', [$start, $end])
        ->pluck('appointment_datetime')
        ->map(fn($d) => Carbon::parse($d)->format('Y-m-d H:i'))
        ->toArray();

    $slots = [];

    // نولّد السلوتات
    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
        $weekday = strtolower($date->format('l'));
        $dayAvailabilities = $availabilities->filter(fn($a) => strtolower($a->day_of_week) === $weekday);

        foreach ($dayAvailabilities as $a) {
            $slotStart = Carbon::parse($date->toDateString() . ' ' . $a->start_time);
            $slotEndLimit = Carbon::parse($date->toDateString() . ' ' . $a->end_time);

            while ($slotStart->copy()->addMinutes($slotMinutes)->lte($slotEndLimit)) {
                if ($slotStart->lt(now())) {
                    $slotStart->addMinutes($slotMinutes);
                    continue;
                }

                $slotKey = $slotStart->format('Y-m-d H:i');
                if (!in_array($slotKey, $booked, true)) {
                    $slots[] = [
                        'datetime' => $slotStart->toDateTimeString(),
                        'date' => $slotStart->toDateString(),
                        'time' => $slotStart->format('H:i'),
                    ];
                }

                $slotStart->addMinutes($slotMinutes);
            }
        }
    }

    return response()->json(['slots' => $slots]);
}

}
//     // جلب جميع المواعيد المتاحة للطبيب
// public function availableSlots(Request $request, $doctorId)
// {
//     $slotMinutes = (int) $request->query('slot', 30); // مدة كل سلوت بالدقائق
//     $startDateStr = $request->query('start_date', now()->toDateString());
//     $endDateStr = $request->query('end_date', now()->addDays(14)->toDateString());

//     $start = Carbon::parse($startDateStr)->startOfDay();
//     $end = Carbon::parse($endDateStr)->endOfDay();

//     // جدول أوقات الطبيب
//     $availabilities = DoctorAvailability::where('doctor_id', $doctorId)->get();

//     // الحجوزات الموجودة مسبقًا
//     $booked = Appointments::where('doctor_id', $doctorId)
//         ->where('status', 'scheduled')
//         ->whereBetween('appointment_datetime', [$start, $end])
//         ->pluck('appointment_datetime')
//         ->map(fn($d) => Carbon::parse($d)->format('Y-m-d H:i'))
//         ->toArray();

//     $slots = [];

//     // نولّد السلوتات
//     for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
//         $weekday = strtolower($date->format('l'));
//         $dayAvailabilities = $availabilities->filter(fn($a) => strtolower($a->day_of_week) === $weekday);

//         foreach ($dayAvailabilities as $a) {
//             $slotStart = Carbon::parse($date->toDateString() . ' ' . $a->start_time);
//             $slotEndLimit = Carbon::parse($date->toDateString() . ' ' . $a->end_time);

//             while ($slotStart->copy()->addMinutes($slotMinutes)->lte($slotEndLimit)) {
//                 if ($slotStart->lt(now())) {
//                     $slotStart->addMinutes($slotMinutes);
//                     continue;
//                 }

//                 $slotKey = $slotStart->format('Y-m-d H:i');
//                 if (!in_array($slotKey, $booked, true)) {
//                     $slots[] = [
//                         'datetime' => $slotStart->toDateTimeString(),
//                         'date' => $slotStart->toDateString(),
//                         'time' => $slotStart->format('H:i'),
//                     ];
//                 }

//                 $slotStart->addMinutes($slotMinutes);
//             }
//         }
//     }

//     return response()->json(['slots' => $slots]);
// }

// // حجز موعد
// public function book(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'doctor_id' => 'required|exists:doctors,id',
//         'service_id' => 'required|exists:center_services,id',
//         'appointment_datetime' => 'required|date_format:Y-m-d H:i',
//         'notes' => 'nullable|string',
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['errors' => $validator->errors()], 422);
//     }

//     $user = auth::user();
//     $doctorId = $request->doctor_id;
//     $appointmentTime = $request->appointment_datetime;

//     // ✅ تحقق: هل الموعد ضمن أوقات توفر الطبيب؟
//     $dayOfWeek = strtolower(Carbon::parse($appointmentTime)->format('l'));
//     $availability = DoctorAvailability::where('doctor_id', $doctorId)
//         ->where('day_of_week', $dayOfWeek)
//         ->first();

//     if (!$availability) {
//         return response()->json(['message' => 'الطبيب غير متاح في هذا اليوم'], 400);
//     }

//     $time = Carbon::parse($appointmentTime)->format('H:i');
//     if ($time < $availability->start_time || $time >= $availability->end_time) {
//         return response()->json(['message' => 'الوقت خارج نطاق التوفر'], 400);
//     }

//     // ✅ تحقق: هل الموعد محجوز مسبقاً؟
//     $exists = Appointments::where('doctor_id', $doctorId)
//         ->where('appointment_datetime', $appointmentTime)
//         ->exists();

//     if ($exists) {
//         return response()->json(['message' => 'الموعد محجوز بالفعل'], 400);
//     }

//     // ✅ إنشاء الحجز
//     $appointment = Appointments::create([
//         'user_id' => $user->id,
//         'doctor_id' => $doctorId,
//         'service_id' => $request->service_id,
//         'appointment_datetime' => $appointmentTime,
//         'status' => 'scheduled',
//         'notes' => $request->notes,
//     ]);

//     return response()->json([
//         'message' => 'تم الحجز بنجاح',
//         'appointment' => $appointment
//     ]);
// }


// }


// namespace App\Http\Controllers;

// use App\Models\Appointments;
// use App\Models\DoctorAvailability;
// use App\Models\Notifications;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Validator;
// use Carbon\Carbon;

// class AppointmentsController extends Controller
// {
//     /**
//      * 🔹 عرض جميع المواعيد المتاحة لطبيب معين
//      */
//     public function availableSlots(Request $request, $doctorId)
//     {
//         $slotMinutes = (int) $request->query('slot', 30); // مدة كل سلوت (افتراضي 30 دقيقة)
//         $startDateStr = $request->query('start_date', now()->toDateString());
//         $endDateStr = $request->query('end_date', now()->addDays(14)->toDateString());

//         $start = Carbon::parse($startDateStr)->startOfDay();
//         $end = Carbon::parse($endDateStr)->endOfDay();

//         // ✅ جدول توفر الطبيب
//         $availabilities = DoctorAvailability::where('doctor_id', $doctorId)->get();

//         // ✅ الحجوزات الموجودة مسبقًا
//         $booked = Appointments::where('doctor_id', $doctorId)
//             ->where('status', 'scheduled')
//             ->whereBetween('appointment_datetime', [$start, $end])
//             ->pluck('appointment_datetime')
//             ->map(fn($d) => Carbon::parse($d)->format('Y-m-d H:i'))
//             ->toArray();

//         $slots = [];

//         // ✅ توليد المواعيد
//         for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
//             $weekday = strtolower($date->format('l'));
//             $dayAvailabilities = $availabilities->filter(fn($a) => strtolower($a->day_of_week) === $weekday);

//             foreach ($dayAvailabilities as $a) {
//                 $slotStart = Carbon::parse($date->toDateString() . ' ' . $a->start_time);
//                 $slotEndLimit = Carbon::parse($date->toDateString() . ' ' . $a->end_time);

//                 while ($slotStart->copy()->addMinutes($slotMinutes)->lte($slotEndLimit)) {
//                     // ⛔ لا تعرض أوقات ماضية
//                     if ($slotStart->lt(now())) {
//                         $slotStart->addMinutes($slotMinutes);
//                         continue;
//                     }

//                     $slotKey = $slotStart->format('Y-m-d H:i');
//                     if (!in_array($slotKey, $booked, true)) {
//                         $slots[] = [
//                             'datetime' => $slotStart->toDateTimeString(),
//                             'date'     => $slotStart->toDateString(),
//                             'time'     => $slotStart->format('H:i'),
//                         ];
//                     }

//                     $slotStart->addMinutes($slotMinutes);
//                 }
//             }
//         }

//         return response()->json(['slots' => $slots]);
//     }

//     /**
//      * 🔹 حجز موعد جديد
//      */
//     public function book(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'doctor_id'            => 'required|exists:doctors,doctor_id',
//             'service_id'           => 'required|exists:services,service_id',
//             'appointment_datetime' => 'required|date_format:Y-m-d H:i',
//             'notes'                => 'nullable|string',
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['errors' => $validator->errors()], 422);
//         }

//         $user = auth()->user();
//         $doctorId = $request->doctor_id;
//         $appointmentTime = $request->appointment_datetime;

//         // ✅ تحقق: هل الموعد ضمن أوقات توفر الطبيب؟
//         $dayOfWeek = strtolower(Carbon::parse($appointmentTime)->format('l'));
//         $availability = DoctorAvailability::where('doctor_id', $doctorId)
//             ->where('day_of_week', $dayOfWeek)
//             ->first();

//         if (!$availability) {
//             return response()->json(['message' => 'الطبيب غير متاح في هذا اليوم'], 400);
//         }

//         $time = Carbon::parse($appointmentTime)->format('H:i');
//         if ($time < $availability->start_time || $time >= $availability->end_time) {
//             return response()->json(['message' => 'الوقت خارج نطاق التوفر'], 400);
//         }

//         // ✅ تحقق: هل الموعد محجوز مسبقًا؟
//         $exists = Appointments::where('doctor_id', $doctorId)
//             ->where('appointment_datetime', $appointmentTime)
//             ->where('status', 'scheduled')
//             ->exists();

//         if ($exists) {
//             return response()->json(['message' => 'الموعد محجوز بالفعل'], 400);
//         }

//         // ✅ إنشاء الموعد
//         $appointment = Appointments::create([
//             'user_id'              => $user->user_id,
//             'doctor_id'            => $doctorId,
//             'service_id'           => $request->service_id,
//             'appointment_datetime' => $appointmentTime,
//             'status'               => 'scheduled',
//             'notes'                => $request->notes,
//         ]);

//         return response()->json([
//             'message'     => 'تم الحجز بنجاح',
//             'appointment' => $appointment
//         ], 201);
//     }

//     /**
//      * 🔹 عرض تفاصيل موعد
//      */
//     public function show($id)
//     {
//         $appointment = Appointments::with(['user', 'doctor', 'service'])->find($id);

//         if (!$appointment) {
//             return response()->json(['message' => 'الموعد غير موجود'], 404);
//         }

//         return response()->json($appointment);
//     }

//     /**
//      * 🔹 إلغاء/حذف موعد
//      */
//     public function cancel($id)
//     {
//         $appointment = Appointments::find($id);

//         if (!$appointment) {
//             return response()->json(['message' => 'الموعد غير موجود'], 404);
//         }

//         $appointment->delete();

//         return response()->json(['message' => 'تم إلغاء الموعد بنجاح']);
//     }
// }


// namespace App\Http\Controllers;

// use App\Models\Appointments;
// use App\Models\DoctorAvailability;
// use App\Models\Notifications;
// use App\Jobs\SendAppointmentReminder;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Validator;
// use Carbon\Carbon;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Auth;

// class AppointmentsController extends Controller
// {
//     /**
//      * ✅ عرض كل الأوقات المتاحة (توليد تلقائي حسب توفر الطبيب)
//      */
//     public function index(Request $request)
//     {
//         $doctorId = $request->query('doctor_id');
//         if (!$doctorId) {
//             return response()->json(['message' => 'يرجى تحديد doctor_id'], 400);
//         }

//         $slotMinutes = 30; // مدة كل سلوت بالدقائق
//         $start = Carbon::now()->startOfDay();
//         $end = Carbon::now()->addDays(14)->endOfDay();

//         $availabilities = DoctorAvailability::where('doctor_id', $doctorId)->get();
//         $booked = Appointments::where('doctor_id', $doctorId)
//             ->where('status', 'scheduled')
//             ->whereBetween('appointment_datetime', [$start, $end])
//             ->pluck('appointment_datetime')
//             ->map(fn($d) => Carbon::parse($d)->format('Y-m-d H:i'))
//             ->toArray();

//         $slots = [];
//         for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
//             $weekday = strtolower($date->format('l'));
//             $dayAvailabilities = $availabilities->filter(fn($a) => strtolower($a->day_of_week) === $weekday);

//             foreach ($dayAvailabilities as $a) {
//                 $slotStart = Carbon::parse($date->toDateString() . ' ' . $a->start_time);
//                 $slotEndLimit = Carbon::parse($date->toDateString() . ' ' . $a->end_time);

//                 while ($slotStart->copy()->addMinutes($slotMinutes)->lte($slotEndLimit)) {
//                     if ($slotStart->lt(now())) {
//                         $slotStart->addMinutes($slotMinutes);
//                         continue;
//                     }

//                     $slotKey = $slotStart->format('Y-m-d H:i');
//                     if (!in_array($slotKey, $booked, true)) {
//                         $slots[] = [
//                             'datetime' => $slotStart->toDateTimeString(),
//                             'date' => $slotStart->toDateString(),
//                             'time' => $slotStart->format('H:i'),
//                         ];
//                     }

//                     $slotStart->addMinutes($slotMinutes);
//                 }
//             }
//         }

//         return response()->json(['slots' => $slots]);
//     }

//     /**
//      * ✅ حجز موعد
//      */
//     public function book(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'doctor_id' => 'required|exists:doctors,id',
//             'service_id' => 'required|exists:center_services,id',
//             'appointment_datetime' => 'required|date_format:Y-m-d H:i',
//             'notes' => 'nullable|string',
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['errors' => $validator->errors()], 422);
//         }

//         $user = Auth::user();
//         $doctorId = $request->doctor_id;
//         $appointmentTime = $request->appointment_datetime;

//         // تحقق: هل الموعد ضمن أوقات توفر الطبيب؟
//         $dayOfWeek = strtolower(Carbon::parse($appointmentTime)->format('l'));
//         $availability = DoctorAvailability::where('doctor_id', $doctorId)
//             ->where('day_of_week', $dayOfWeek)
//             ->first();

//         if (!$availability) {
//             return response()->json(['message' => 'الطبيب غير متاح في هذا اليوم'], 400);
//         }

//         $time = Carbon::parse($appointmentTime)->format('H:i');
//         if ($time < $availability->start_time || $time >= $availability->end_time) {
//             return response()->json(['message' => 'الوقت خارج نطاق التوفر'], 400);
//         }

//         // تحقق: هل الموعد محجوز مسبقاً؟
//         $exists = Appointments::where('doctor_id', $doctorId)
//             ->where('appointment_datetime', $appointmentTime)
//             ->where('status', 'scheduled')
//             ->exists();

//         if ($exists) {
//             return response()->json(['message' => 'الموعد محجوز بالفعل'], 409);
//         }

//         // إنشاء الحجز داخل Transaction
//         DB::beginTransaction();
//         try {
//             $appointment = Appointments::create([
//                 'user_id' => $user->id,
//                 'doctor_id' => $doctorId,
//                 'service_id' => $request->service_id,
//                 'appointment_datetime' => $appointmentTime,
//                 'status' => 'scheduled',
//                 'notes' => $request->notes,
//             ]);

//             // إشعار تأكيد للمريض
//             Notifications::create([
//                 'user_id' => $appointment->user_id,
//                 'message_text' => "تم حجز موعدك مع الدكتور {$appointment->doctor->name} بتاريخ " .
//                                   Carbon::parse($appointment->appointment_datetime)->format('Y-m-d H:i') . ".",
//                 'is_read' => false,
//                 'type' => 'confirmation',
//             ]);

//             // جدول التذكير قبل ساعتين
//             $reminderTime = Carbon::parse($appointment->appointment_datetime)->subHours(2);
//             SendAppointmentReminder::dispatch($appointment)->delay($reminderTime);

//             DB::commit();
//             return response()->json(['message' => 'تم الحجز بنجاح', 'appointment' => $appointment], 201);

//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json(['message' => 'خطأ أثناء الحجز', 'error' => $e->getMessage()], 500);
//         }
//     }

//     /**
//      * ✅ عرض تفاصيل موعد واحد
//      */
//     public function show($id)
//     {
//         $appointment = Appointments::with(['user', 'doctor', 'service'])->find($id);

//         if (!$appointment) {
//             return response()->json(['message' => 'الموعد غير موجود'], 404);
//         }

//         return response()->json($appointment);
//     }

//     /**
//      * ✅ تعديل موعد
//      */
//     public function update(Request $request, $id)
//     {
//         $appointment = Appointments::find($id);
//         if (!$appointment) {
//             return response()->json(['message' => 'الموعد غير موجود'], 404);
//         }

//         $validator = Validator::make($request->all(), [
//             'doctor_id' => 'sometimes|exists:doctors,id',
//             'service_id' => 'sometimes|exists:center_services,id',
//             'appointment_datetime' => 'sometimes|date_format:Y-m-d H:i',
//             'status' => 'sometimes|in:scheduled,completed,cancelled,no_show',
//             'notes' => 'nullable|string',
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['errors' => $validator->errors()], 422);
//         }

//         $appointment->update($request->all());
//         return response()->json(['message' => 'تم تعديل الموعد بنجاح', 'appointment' => $appointment]);
//     }

//     /**
//      * ✅ حذف موعد
//      */
//     public function destroy($id)
//     {
//         $appointment = Appointments::find($id);
//         if (!$appointment) {
//             return response()->json(['message' => 'الموعد غير موجود'], 404);
//         }

//         $appointment->delete();
//         return response()->json(['message' => 'تم حذف الموعد بنجاح']);
//     }
// }


// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use App\Models\Appointments;
// use App\Models\User;
// use App\Models\Doctor;
// use App\Models\Services;
// use Carbon\Carbon;
// use Illuminate\Support\Facades\Notification;
// use Illuminate\Support\Facades\Validator;
// class AppointmentsController extends Controller
// {
//     // 1️⃣ جلب جميع المواعيد
//     public function index()
//     {
//         $appointments = Appointments::with(['user', 'doctor', 'service'])->get();
//         return response()->json($appointments);
//     }

//     // 2️⃣ حجز موعد جديد (المريض)
//      public function store(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'user_id'              => 'required|exists:users,user_id',
//             'doctor_id'            => 'required|exists:doctors,doctor_id',
//             'service_id'           => 'required|exists:services,service_id',
//             'appointment_datetime' => 'required|date',
//             'notes'                => 'nullable|string',
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['errors' => $validator->errors()], 422);
//         }

//         // ✅ التحقق إذا الموعد محجوز مسبقًا
//         $exists = Appointments::where('doctor_id', $request->doctor_id)
//             ->where('appointment_datetime', $request->appointment_datetime)
//             ->where('status', 'scheduled')
//             ->exists();

//         if ($exists) {
//             return response()->json(['message' => 'الموعد غير متاح'], 400);
//         }

//         // ✅ إنشاء الموعد
//         $appointment = Appointments::create([
//             'user_id'              => $request->user_id,
//             'doctor_id'            => $request->doctor_id,
//             'service_id'           => $request->service_id,
//             'appointment_datetime' => $request->appointment_datetime,
//             'status'               => 'scheduled',
//             'notes'                => $request->notes,
//         ]);

//         // ✅ إشعار تأكيد للمريض
//         Notifications::create([
//             'user_id'      => $appointment->user_id,
//             'message_text' => "تم حجز موعدك مع الدكتور {$appointment->doctor->name} بتاريخ " .
//                               Carbon::parse($appointment->appointment_datetime)->format('Y-m-d H:i') . ".",
//             'is_read'      => false,
//             'type'         => 'confirmation',
//         ]);

//         // ✅ جدولة تذكير قبل ساعتين
//         $reminderTime = Carbon::parse($appointment->appointment_datetime)->subHours(2);
//         SendAppointmentReminder::dispatch($appointment)->delay($reminderTime);

//         return response()->json([
//             'message'     => 'تم الحجز بنجاح',
//             'appointment' => $appointment
//         ], 201);
//     }

//     // 4️⃣ تعديل موعد
//     public function update(Request $request, $appointment_id)
//     {
//         $appointment = Appointments::where('appointment_id', $appointment_id)->first();
//         if (!$appointment) {
//             return response()->json(['message' => 'الموعد غير موجود'], 404);
//         }

//         $request->validate([
//             'doctor_id' => 'sometimes|exists:doctors,doctor_id',
//             'user_id' => 'sometimes|exists:users,user_id',
//             'service_id' => 'sometimes|exists:services,service_id',
//             'appointment_datetime' => 'sometimes|date',
//             'status' => 'sometimes|in:scheduled,cancelled,done',
//         ]);

//         $appointment->update($request->only([
//             'doctor_id',
//             'user_id',
//             'service_id',
//             'appointment_datetime',
//             'status',
//         ]));

//         // إشعار عند التعديل
//         Notification::send($appointment->user, new FirebaseNotification([
//             'title' => 'تم تعديل موعدك',
//             'body' => "تم تعديل موعدك مع الطبيب رقم {$appointment->doctor_id}",
//             'type' => 'appointment_updated',
//             'appointment_id' => $appointment->appointment_id,
//         ]));

//         return response()->json($appointment);
//     }

//     // 5️⃣ حذف / إلغاء موعد
//     public function destroy($appointment_id)
//     {
//         $appointment = Appointments::where('appointment_id', $appointment_id)->first();
//         if (!$appointment) {
//             return response()->json(['message' => 'الموعد غير موجود'], 404);
//         }

//         // إشعار عند الحذف
//         Notification::send($appointment->user, new FirebaseNotification([
//             'title' => 'تم إلغاء موعدك',
//             'body' => "تم إلغاء موعدك مع الطبيب رقم {$appointment->doctor_id}",
//             'type' => 'appointment_cancelled',
//             'appointment_id' => $appointment->appointment_id,
//         ]));

//         $appointment->delete();
//         return response()->json(['message' => 'تم حذف الموعد']);
//     }

//     // 6️⃣ جلب السلوات المتاحة لطبيب معين
//     public function availableSlots($doctor_id)
//     {
//         $doctor = Doctor::find($doctor_id);
//         if (!$doctor) {
//             return response()->json(['message' => 'الطبيب غير موجود'], 404);
//         }

//         // مثال: كل الساعات من 9 صباحاً إلى 5 مساءً
//         $slots = [];
//         for ($hour = 9; $hour <= 17; $hour++) {
//             $time = Carbon::createFromTime($hour, 0, 0)->toTimeString();
//             $exists = Appointments::where('doctor_id', $doctor_id)
//                 ->whereTime('appointment_datetime', $time)
//                 ->first();
//             if (!$exists) {
//                 $slots[] = $time;
//             }
//         }

//         return response()->json([
//             'doctor_id' => $doctor_id,
//             'available_slots' => $slots,
//         ]);
//     }
// }
