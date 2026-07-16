<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Emails a customer their passwordless sign-in link. Queued on the mail queue
 * so requesting a link never blocks the response.
 */
final class CustomerLoginLink extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $token,
    ) {
        $this->onQueue('mail');
    }

    /**
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your sign-in link')
            ->greeting("Hello {$notifiable->name},")
            ->line('Use the button below to sign in to support. This link can be used once and expires shortly.')
            ->action('Sign in', route('portal.login.verify', ['token' => $this->token]))
            ->line('If you did not request this, you can safely ignore this email.');
    }
}
