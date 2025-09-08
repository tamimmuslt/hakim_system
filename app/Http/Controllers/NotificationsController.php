<?php



namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;   // جديد

class NotificationsController extends Controller
{
    // جلب كل الإشعارات
    public function index()
    {
        $notifications = Notifications::with('user')->get();
        return response()->json($notifications);
    }

    // إنشاء إشعار جديد + إرساله عبر Firebase
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'      => 'required|exists:users,user_id',
            'message_text' => 'required|string',
            'device_token' => 'required|string', // جديد: لازم تبعث التوكين من تطبيق الموبايل
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 1️⃣ تخزين الإشعار بقاعدة البيانات
        $notification = Notifications::create($validator->validated());

        // 2️⃣ إرسال الإشعار عبر Firebase
        try {
            $factory = (new Factory)->withServiceAccount(config('firebase.credentials.file'));
            $messaging = $factory->createMessaging();

            $message = [
                'token' => $request->device_token, // التوكين الخاص بموبايل المستخدم
                'notification' => [
                    'title' => 'إشعار جديد',
                    'body'  => $request->message_text,
                ],
                'data' => [
                    'notification_id' => (string) $notification->notification_id,
                    'user_id'         => (string) $request->user_id,
                ],
            ];

            $messaging->send($message);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Notification saved but failed to send via Firebase',
                'error'   => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Notification created and sent successfully',
            'data'    => $notification
        ], 201);
    }

    // جلب إشعار محدد
    public function show($id)
    {
        $notification = Notifications::with('user')->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        return response()->json($notification);
    }

    // تعليم إشعار كمقروء
    public function markAsRead($id)
    {
        $notification = Notifications::find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notification marked as read']);
    }

    // حذف إشعار
    public function destroy($id)
    {
        $notification = Notifications::find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully']);
    }
}
