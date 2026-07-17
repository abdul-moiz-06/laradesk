<?php

declare(strict_types=1);

namespace App\Livewire\Portal;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The customer's own ticket list. Scoped to the signed-in customer through the
 * forCustomer query scope, on top of the tenant fence, so a customer only ever
 * sees their own tickets.
 */
#[Layout('components.portal.layout', ['title' => 'My tickets'])]
final class TicketList extends Component
{
    public function render(): View
    {
        $tickets = Ticket::forCustomer($this->customer())->latest()->get();

        return view('livewire.portal.ticket-list', ['tickets' => $tickets]);
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
