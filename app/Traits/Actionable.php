<?php

namespace App\Traits;

use App\Models\Action;

/**
 * Trait Actionable
 */
trait Actionable
{
    /**
     * ----------------------------------------
     * ----------------------------------------
     * Actions - that were performed on this model
     * --------------------------------------------
     */
    public function actions()
    {
        return $this->morphMany(Action::class, 'actionable', 'actionable_type', 'actionable_id', 'id');
    }
}
