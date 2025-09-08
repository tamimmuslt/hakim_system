<?php
namespace App\Http\Controllers;

use App\Models\Reports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    // إضافة إبلاغ
public function store(Request $request)
{
    $request->validate([
        'reportable_type' => 'required|string|in:doctor,center',
        'reportable_id' => 'required|integer',
        'reason' => 'required|string|max:1000',
    ]);

    $reportableClass = $request->reportable_type === 'doctor' 
        ? \App\Models\Doctor::class 
        : \App\Models\Centers::class;

    $report = Reports::create([
        'user_id' => Auth::id(),
        'reason' => $request->reason,
        'reportable_type' => $reportableClass,
        'reportable_id' => $request->reportable_id,
    ]);

    // ⬇ Validator للإشعار
    $notificationData = [
        'user_id' => Auth::id(),
        'message_text' => 'تم استلام إبلاغك وسيتم معالجته من قبل فريق الدعم.',
        'is_read' => false,
    ];

    $validator = Validator::make($notificationData, [
        'user_id' => 'required|exists:users,user_id',
        'message_text' => 'required|string|max:1000',
        'is_read' => 'boolean',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    \App\Models\Notifications::create($validator->validated());

    return response()->json([
        'message' => 'تم إرسال الإبلاغ بنجاح',
        'report' => $report
    ], 201);
}

    // تحديث حالة الإبلاغ
   public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:pending,reviewed,rejected'
    ]);

    $report = Reports::findOrFail($id);
    $report->update(['status' => $request->status]);

    $msg = $request->status === 'reviewed' 
        ? 'تمت مراجعة إبلاغك وسيتم اتخاذ الإجراءات اللازمة.'
        : 'تم رفض الإبلاغ بعد المراجعة.';

    $notificationData = [
        'user_id' => $report->user_id,
        'message_text' => $msg,
        'is_read' => false,
    ];

    $validator = Validator::make($notificationData, [
        'user_id' => 'required|exists:users,user_id',
        'message_text' => 'required|string|max:1000',
        'is_read' => 'boolean',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    \App\Models\Notifications::create($validator->validated());

    return response()->json(['message' => 'تم تحديث حالة الإبلاغ']);
}

}
