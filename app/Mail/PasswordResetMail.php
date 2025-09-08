<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PasswordResetCode extends Notification
{
    use Queueable;

    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    // طريقة الإرسال
    public function via($notifiable)
    {
        return ['mail']; // ممكن تضيف 'database', 'sms' لاحقًا
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
                    ->subject('Password Reset Code')
                    ->greeting('مرحبًا ' . $notifiable->name)
                    ->line('كود إعادة تعيين كلمة المرور الخاص بك هو: ' . $this->code)
                    ->line('هذا الرمز صالح لمدة 15 دقيقة.');
    }

    // إذا أردت تخزين الإشعار في قاعدة البيانات
    public function toArray($notifiable)
    {
        return [
            'code' => $this->code
        ];
    }
}
