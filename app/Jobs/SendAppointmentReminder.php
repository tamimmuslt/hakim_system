<?php
namespace App\Jobs;

use App\Models\Notifications;
use App\Models\Appointments;
use Kreait\Firebase\Factory;

class SendAppointmentReminder extends Job
{
    protected $appointment;

    public function __construct(Appointments $appointment)
    {
        $this->appointment = $appointment;
    }

    public function handle()
    {
        $patient = $this->appointment->patient->user;
        $doctor  = $this->appointment->doctor->user;

        $messageText = "تذكير: عندك موعد مع الدكتور {$doctor->name} بتاريخ {$this->appointment->date} الساعة {$this->appointment->time} (بعد ساعتين).";

        // 1️⃣ تخزين بقاعدة البيانات
        $notification = Notifications::create([
            'user_id'      => $patient->user_id,
            'message_text' => $messageText,
            'is_read'      => false,
        ]);

        // 2️⃣ إرسال عبر Firebase
        try {
            $factory = (new Factory)->withServiceAccount(config('firebase.credentials.file'));
            $messaging = $factory->createMessaging();

            $message = [
                'token' => $patient->device_token,
                'notification' => [
                    'title' => 'تذكير بالموعد',
                    'body'  => $messageText,
                ],
            ];

            $messaging->send($message);

        } catch (\Exception $e) {
            \Log::error("Firebase error: " . $e->getMessage());
        }
    }
}
