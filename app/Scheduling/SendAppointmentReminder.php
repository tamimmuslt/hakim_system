<?php

namespace App\Scheduling;

use Illuminate\Console\Scheduling\Schedule;
use App\Models\Appointments;
use App\Models\Notifications;
use Carbon\Carbon;

class SendAppointmentReminder
{
    public function __invoke(Schedule $schedule): void
    {
        $schedule->call(function () {
            $now = Carbon::now();
            $targetTime = $now->copy()->addHours(2);

            $appointments = Appointments::whereBetween('appointment_time', [
                $targetTime->startOfMinute(),
                $targetTime->endOfMinute()
            ])->with('user')->get();

            foreach ($appointments as $appointment) {
                Notifications::create([
                    'user_id'      => $appointment->user_id,
                    'message_text' => 'تذكير: لديك موعد بعد ساعتين في ' . $appointment->appointment_time->format('H:i d/m/Y'),
                ]);
            }
        })->everyMinute();
    }
}
