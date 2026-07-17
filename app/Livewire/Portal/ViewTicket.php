<?php

declare(strict_types=1);

namespace App\Livewire\Portal;

use App\Actions\Tickets\ReplyToTicket;
use App\DTOs\ReplyToTicketDTO;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * A single ticket and its reply thread, for the owning customer. The ticket is
 * always re-resolved through the forCustomer scope, so ownership is enforced on
 * every request, not just on mount.
 */
#[Layout('components.portal.layout', ['title' => 'Ticket'])]
final class ViewTicket extends Component
{
    public string $ulid = '';

    #[Validate('required|string|max:5000')]
    public string $body = '';

    public function mount(string $ulid): void
    {
        $this->ulid = $ulid;
        $this->ticket();
    }

    public function reply(ReplyToTicket $replyToTicket): void
    {
        $this->validate();

        $ticket = $this->ticket();

        if ($ticket->status === TicketStatus::Closed) {
            return;
        }

        $replyToTicket(new ReplyToTicketDTO(
            ticketId: $ticket->id,
            userId: $this->customer()->id,
            body: $this->body,
        ));

        $this->body = '';
    }

    public function render(): View
    {
        $ticket = $this->ticket();
        $ticket->load('publishedReplies.author');

        return view('livewire.portal.view-ticket', ['ticket' => $ticket]);
    }

    private function ticket(): Ticket
    {
        return Ticket::forCustomer($this->customer())
            ->where('ulid', $this->ulid)
            ->firstOrFail();
    }

    private function customer(): User
    {
        $customer = Auth::guard('customer')->user();

        if (! $customer instanceof User) {
            abort(403);
        }

        return $customer;
    }
}
