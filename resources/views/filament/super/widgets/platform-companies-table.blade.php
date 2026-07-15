<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Companies</x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="text-gray-500 dark:text-gray-400">
                        <th class="py-2 pr-4 font-medium">Company</th>
                        <th class="py-2 pr-4 font-medium">Plan</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 pr-4 font-medium text-right">Users</th>
                        <th class="py-2 pr-4 font-medium text-right">Tickets</th>
                        <th class="py-2 pr-4 font-medium text-right">Open</th>
                        <th class="py-2 font-medium text-right">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                    @forelse ($this->getCompanies() as $company)
                        @php
                            $statusColor = match ($company->status) {
                                \App\Enums\TenantStatus::Active => 'success',
                                \App\Enums\TenantStatus::Suspended => 'danger',
                                default => 'warning',
                            };
                        @endphp
                        <tr>
                            <td class="py-2 pr-4 font-medium text-gray-950 dark:text-white">{{ $company->name }}</td>
                            <td class="py-2 pr-4">{{ $company->plan ?? 'None' }}</td>
                            <td class="py-2 pr-4">
                                <x-filament::badge :color="$statusColor" class="inline-flex">
                                    {{ ucfirst($company->status->value) }}
                                </x-filament::badge>
                            </td>
                            <td class="py-2 pr-4 text-right tabular-nums">{{ $company->usersCount }}</td>
                            <td class="py-2 pr-4 text-right tabular-nums">{{ $company->ticketsCount }}</td>
                            <td class="py-2 pr-4 text-right tabular-nums">{{ $company->openTicketsCount }}</td>
                            <td class="py-2 text-right whitespace-nowrap">{{ $company->createdAt->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4 text-center text-gray-500 dark:text-gray-400">
                                No companies yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
