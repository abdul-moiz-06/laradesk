<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Tickets\ReplyToTicket;
use App\DTOs\ReplyToTicketDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTicketReplyRequest;
use App\Http\Resources\TicketReplyResource;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class TicketReplyController extends Controller
{
    public function store(StoreTicketReplyRequest $request, Ticket $ticket, ReplyToTicket $replyToTicket): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $reply = $replyToTicket(new ReplyToTicketDTO(
            ticketId: $ticket->id,
            userId: $user->id,
            body: $request->string('body')->toString(),
        ));

        $reply->load('author');

        return TicketReplyResource::make($reply)->response()->setStatusCode(201);
    }
}
