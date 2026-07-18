<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogService
{
    public function record(?User $user, string $action, string $entityType, ?int $entityId = null, ?array $old = null, ?array $new = null): void
    {
        AuditLog::create(['user_id' => $user?->id, 'action' => $action, 'entity_type' => $entityType, 'entity_id' => $entityId, 'old_values' => $old, 'new_values' => $new, 'ip_address' => app()->runningInConsole() ? null : request()->ip(), 'user_agent' => app()->runningInConsole() ? null : request()->userAgent(), 'created_at' => now()]);
    }
}
