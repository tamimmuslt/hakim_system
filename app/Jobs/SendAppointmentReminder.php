<?php

namespace App\Jobs;

use App\Models\Notifications;
use App\Models\Appointments;
use Kreait\Firebase\Factory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class SendAppointmentReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Appointments $appointment;

    public function __construct(Appointments $appointment)
    {
        // من الأفضل تمرير الـ id بدل الموديل لو تواجه مشاكل serialization
        $this->appointment = $appointment;
    }

    public function handle()
    {
        // احرص أنك تعيد تحميل الـ model داخل الـ job لضمان عدم مشاكل serializing relations
        $appointment = Appointments::with(['user','doctor'])->find($this->appointment->appointment_id);

        if (! $appointment) {
            \Log::warning("SendAppointmentReminder: appointment not found id={$this->appointment->appointment_id}");
            return;
        }

        $patient = $appointment->user;
        $doctor  = $appointment->doctor;

        $dateTime = Carbon::parse($appointment->appointment_datetime)->format('Y-m-d H:i');
        $messageText = "تذكير: عندك موعد مع الدكتور {$doctor->name} بتاريخ {$dateTime} (بعد ساعتين).";

        Notifications::create([
            'user_id'      => $patient->user_id,
            'message_text' => $messageText,
            'is_read'      => false,
            'type'         => 'reminder',
        ]);

        try {
            $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials'));
            $messaging = $factory->createMessaging();

            $message = [
                'token' => $patient->device_token ?? null,
                'notification' => [
                    'title' => 'تذكير بالموعد',
                    'body'  => $messageText,
                ],
            ];

            if (!empty($message['token'])) {
                $messaging->send($message);
            } else {
                \Log::warning("SendAppointmentReminder: no device token for user {$patient->user_id}");
            }
        } catch (\Throwable $e) {
            \Log::error("Firebase error in SendAppointmentReminder: " . $e->getMessage());
        }
    }
}
