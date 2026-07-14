<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Tickets\CreateTicket;
use App\DTOs\CreateTicketDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TicketController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Ticket::class);

        /** @var User $user */
        $user = $request->user();

        $tickets = Ticket::forCustomer($user)->latest()->paginate(15);

        return TicketResource::collection($tickets);
    }

    public function store(StoreTicketRequest $request, CreateTicket $createTicket): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $ticket = $createTicket(new CreateTicketDTO(
            title: $request->string('title')->toString(),
            description: $request->string('description')->toString(),
            customerId: $user->id,
        ));

        return TicketResource::make($ticket)->response()->setStatusCode(201);
    }

    public function show(Request $request, Ticket $ticket): TicketResource
    {
        $this->authorize('view', $ticket);

        $ticket->load('publishedReplies.author');

        return TicketResource::make($ticket);
    }
}
