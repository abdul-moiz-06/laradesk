<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketSentiment;
use App\Enums\TicketStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasPublicUlid;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int $customer_id
 * @property int|null $assigned_agent_id
 * @property string $title
 * @property string $description
 * @property TicketStatus $status
 * @property TicketPriority|null $priority
 * @property TicketCategory|null $category
 * @property TicketSentiment|null $sentiment
 * @property string|null $ai_draft_reply
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read Tenant $tenant
 * @property-read User $customer
 * @property-read User|null $assignedAgent
 * @property-read Collection<int, TicketReply> $replies
 * @property-read Collection<int, TicketReply> $publishedReplies
 *
 * @method static Builder<Ticket> forCustomer(User $customer)
 */
#[Fillable([
    'title', 'description', 'status', 'priority', 'category',
    'sentiment', 'ai_draft_reply', 'customer_id', 'assigned_agent_id',
])]
class Ticket extends Model
{
    use BelongsToTenant, HasPublicUlid;

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    /**
     * @return HasMany<TicketReply, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    /**
     * Replies visible to the customer — excludes internal, unsent AI drafts.
     *
     * @return HasMany<TicketReply, $this>
     */
    public function publishedReplies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->where('is_ai_draft', false);
    }

    /**
     * Limit the query to tickets owned by the given customer.
     *
     * @param  Builder<Ticket>  $query
     */
    #[Scope]
    protected function forCustomer(Builder $query, User $customer): void
    {
        $query->where('customer_id', $customer->id);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'category' => TicketCategory::class,
            'sentiment' => TicketSentiment::class,
        ];
    }
}
