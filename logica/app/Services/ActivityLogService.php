<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    public static function log(
        string $entityType,
        int $entityId,
        string $action,
        array $before = null,
        array $after = null,
        int $performedBy = null
    ): void {
        ActivityLog::create([
            'entity_type'  => $entityType,
            'entity_id'    => $entityId,
            'action'       => $action,
            'before'       => $before,
            'after'        => $after,
            'performed_by' => $performedBy,
        ]);
    }
}
