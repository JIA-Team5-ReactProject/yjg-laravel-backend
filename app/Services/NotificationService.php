<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\Notification;

class NotificationService
{
    // Services..
    public function __construct(protected Messaging $messaging)
    {
    }

    /**
     * @param string $title
     * @param string $body
     * @param string $token
     * @param string $page
     * @param int $id
     * @return array
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function postNotification(string $title, string $body, string $token, string $page, int $id = null): array
    {
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create($title, $body))
            ->withData([
                'page' => $page,
                'id'   => $id,
            ]);
        return $this->messaging->send($message);
    }

    /**
     * @param string $title
     * @param string $body
     * @param array $tokens
     * @param string $page
     * @param int $id
     * @return MulticastSendReport
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function postNotificationMulticast(string $title, string $body, array $tokens, string $page, int $id = null): \Kreait\Firebase\Messaging\MulticastSendReport
    {
        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body))
            ->withData([
                'page' => $page,
                'id'   => $id,
            ]);
        return $this->messaging->sendMulticast($message, $tokens);
    }
}
