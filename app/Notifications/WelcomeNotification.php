<?php

// namespace App\Notifications;

// use Illuminate\Bus\Queueable;
// use Illuminate\Notifications\Notification;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Notifications\Messages\MailMessage;

// class WelcomeNotification extends Notification
// {
//     use Queueable;

//     protected $user;
//     protected $code;

//     /**
//      * Create a new notification instance.
//      */
//     public function __construct($user, $code = null)
//     {
//         $this->user = $user;
//         $this->code = $code; 
//     }

//        public function via($notifiable)
//     {
//         return ['mail'];
//     }

//     public function toMail($notifiable)
//     {
//         $mail = (new MailMessage)
//             ->subject('🎉 مرحبًا بك في منصتنا الطبية!')
//             ->greeting("أهلًا بك يا {$this->user->name} 👋")
//             ->line('نحن سعداء بانضمامك إلى منصتنا الطبية.');

//         // إذا كان المستخدم مريضًا وفيه كود تفعيل
//         if ($this->user->user_type === 'patient' && $this->code) {
//             $mail->line('🔐 رمز التفعيل الخاص بك هو:')
//                  ->line("👉 **{$this->code}**")
//                  ->line('يرجى استخدام هذا الرمز لتفعيل بريدك الإلكتروني.');
//         }

//         $mail->line('نتمنى لك تجربة صحية مميزة معنا 🏥')
//              ->salutation('مع تحيات فريق الدعم 👨‍⚕️');

//         return $mail;
//     }
// }


namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeNotification extends Notification
{
    use Queueable;

    protected $user;
    protected $code;

    public function __construct($user, $code = null)
    {
        $this->user = $user;
        $this->code = $code; 
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('🎉 مرحبًا بك في ' . config('app.name'))
            ->markdown('emails.welcome', [
                'user' => $this->user,
                'code' => $this->code,
                'appName' => config('app.name'),
                'logo' => asset('images/logo.png'),
            ]);
    }
}
