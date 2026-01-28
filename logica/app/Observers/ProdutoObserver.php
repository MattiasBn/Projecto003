<?php

namespace App\Observers;

use App\Models\Produto;

class ProdutoObserver
{
    /**
     * Handle the Produto "created" event.
     */
    public function created(Produto $produto): void
    {
         ActivityLog::create([
        'entity_type' => 'produto',
        'entity_id' => $produto->id,
        'action' => 'updated',
        'before' => $produto->getOriginal(),
        'after' => $produto->getAttributes(),
        'performed_by' => Auth::id(),
    ]);
    
    }

    /**
     * Handle the Produto "updated" event.
     */
    public function updated(Produto $produto): void
    {
        //
    }

    /**
     * Handle the Produto "deleted" event.
     */
    public function deleted(Produto $produto): void
    {
        //
    }

    /**
     * Handle the Produto "restored" event.
     */
    public function restored(Produto $produto): void
    {
        //
    }

    /**
     * Handle the Produto "force deleted" event.
     */
    public function forceDeleted(Produto $produto): void
    {
        //
    }
}
