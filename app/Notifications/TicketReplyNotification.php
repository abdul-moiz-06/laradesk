<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Filament\Resources\Tickets\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Tells the assigned agent a new reply has landed on their ticket (from the
 * customer), by email and in the panel notification bell. Queued on the mail
 * queue so it never blocks the request.
 */
final class TicketReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
    ) {
        $this->onQueue('mail');
    }

    /**
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New reply on a ticket assigned to you')
            ->greeting("Hello {$notifiable->name},")
            ->line("There is a new reply on the ticket: {$this->ticket->title}")
            ->action('View ticket', TicketResource::getUrl('view', ['record' => $this->ticket]))
            ->line('Please take a look when you can.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title('New reply on your ticket')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->body(Str::limit($this->ticket->title, 60))
            ->actions([
                Action::make('view')
                    ->url(TicketResource::getUrl('view', ['record' => $this->ticket]))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
