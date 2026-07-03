<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as NotificationsResetPassword;
use Illuminate\Bus\Queueable;

class ResetPasswordNotification extends NotificationsResetPassword
{
    use Queueable;

    protected string $route;

    public function __construct(#[\SensitiveParameter] string $token, string $route)
    {
        parent::__construct($token);

        $this->route = $route;
    }

    protected function resetUrl($notifiable)
    {
        return url(route($this->route, [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
