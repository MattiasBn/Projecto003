<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
        protected $fillable = [
        'entity_type',
        'entity_id',
        'action',
        'before',
        'after',
        'performed_by',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];
}
