<?php
// app/Services/FcmService.php
namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FcmService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials'));
        $this->messaging = $factory->createMessaging();
    }

    public function sendToTokens(array $tokens, string $title, string $body, array $data = [])
    {
        if (empty($tokens)) {
            return 'No tokens registered';
        }

        $notification = Notification::create($title, $body);

        $message = CloudMessage::new()->withNotification($notification)->withData($data);

        $report = $this->messaging->sendMulticast($message, $tokens);

        return [
            'success_count' => $report->successes()->count(),
            'failure_count' => $report->failures()->count(),
        ];
    }
}
