<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function logsUser($userId)
    {
        return ActivityLog::where('entity_type', 'user')
            ->where('entity_id', $userId)
            ->latest()
            ->get();
    }

    public function logsProduto($produtoId)
    {
        return ActivityLog::where('entity_type', 'produto')
            ->where('entity_id', $produtoId)
            ->latest()
            ->get();
    }

    public function show($id)
    {
        return ActivityLog::findOrFail($id);
    }
}
