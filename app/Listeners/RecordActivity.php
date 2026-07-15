<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\AuditableEvent;

/**
 * Records one audit entry for every AuditableEvent. Runs synchronously so the
 * trail is guaranteed (no action passes unaudited), and captures forensic
 * context (who is the auto-detected causer; where is the IP and user agent).
 */
final class RecordActivity
{
    public function handle(AuditableEvent $event): void
    {
        activity()
            ->event(class_basename($event))
            ->performedOn($event->auditSubject())
            ->withProperties(array_merge($event->auditProperties(), $this->requestContext()))
            ->log($event->auditDescription());
    }

    /**
     * @return array<string, string|null>
     */
    private function requestContext(): array
    {
        $request = request();

        return [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }
}
