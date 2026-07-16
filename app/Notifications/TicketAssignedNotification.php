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
 * Tells an agent a ticket has been assigned to them, by email and in the panel
 * notification bell. Queued on the mail queue so it never blocks the request.
 */
final class TicketAssignedNotification extends Notification implements ShouldQueue
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
            ->subject('A ticket was assigned to you')
            ->greeting("Hello {$notifiable->name},")
            ->line("You have been assigned the ticket: {$this->ticket->title}")
            ->action('View ticket', TicketResource::getUrl('view', ['record' => $this->ticket]))
            ->line('Please review and respond when you can.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Ticket assigned to you')
            ->icon('heroicon-o-inbox-arrow-down')
            ->body(Str::limit($this->ticket->title, 60))
            ->actions([
                Action::make('view')
                    ->url(TicketResource::getUrl('view', ['record' => $this->ticket]))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
