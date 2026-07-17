<?php

declare(strict_types=1);

namespace App\Actions\Portal;

use App\Actions\Tickets\CreateTicket;
use App\DTOs\CreateTicketDTO;
use App\Enums\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Handles a public ticket submission: finds or creates the customer within the
 * company, opens the ticket (which triggers AI triage), and emails the customer
 * a sign-in link to track it. The customer and ticket are written atomically.
 *
 * Returns false when the email is already registered elsewhere and cannot be
 * used here, true otherwise.
 */
final class SubmitPortalTicket
{
    public function __construct(
        private readonly CreateTicket $createTicket,
        private readonly RequestLoginLink $requestLoginLink,
    ) {}

    public function __invoke(Tenant $tenant, string $name, string $email, string $subject, string $message): bool
    {
        $email = Str::lower(trim($email));
        $existing = User::query()->where('email', $email)->first();

        if ($existing !== null
            && ($existing->tenant_id !== $tenant->id || ! $existing->hasRole(Role::Customer->value))) {
            return false;
        }

        DB::transaction(function () use ($tenant, $name, $email, $subject, $message, $existing): void {
            $customer = $existing ?? $this->createCustomer($tenant, $name, $email);

            ($this->createTicket)(new CreateTicketDTO(
                title: $subject,
                description: $message,
                customerId: $customer->id,
            ));
        });

        ($this->requestLoginLink)($email);

        return true;
    }

    private function createCustomer(Tenant $tenant, string $name, string $email): User
    {
        $customer = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Str::random(40),
            'tenant_id' => $tenant->id,
        ]);

        $customer->assignRole(Role::Customer->value);

        return $customer;
    }
}
