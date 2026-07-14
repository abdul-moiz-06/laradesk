<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $ticket_id
 * @property int $user_id
 * @property string $body
 * @property bool $is_ai_draft
 * @property CarbonImmutable $created_at
 * @property-read Tenant $tenant
 * @property-read Ticket $ticket
 * @property-read User $author
 */
#[Fillable(['ticket_id', 'user_id', 'body', 'is_ai_draft'])]
class TicketReply extends Model
{
    use BelongsToTenant;

    /**
     * @return BelongsTo<Ticket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_ai_draft' => 'boolean',
        ];
    }
}
