<?php

namespace App\Observers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
         ActivityLog::create([
        'entity_type' => 'user',
        'entity_id' => $user->id,
        'action' => 'updated',
        'before' => $user->getOriginal(),
        'after' => $user->getAttributes(),
        'performed_by' => Auth::id(),
    ]);

    }

    /**
     * Handle the User "updated" event.
     */
    

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
