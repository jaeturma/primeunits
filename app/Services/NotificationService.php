<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PrimeUnitsNotification;

class NotificationService
{
    public function send(User $user, string $event, string $title, string $message, ?string $url = null): void
    {
        $user->notify(new PrimeUnitsNotification(
            event: $event,
            title: $title,
            message: $message,
            url: $url,
        ));
    }
}
