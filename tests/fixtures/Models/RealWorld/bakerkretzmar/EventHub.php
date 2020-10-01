<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EventHub extends Pivot
{
    public $incrementing = true;

    protected $appends = [
        'is_accepted',
        'is_declined',
        'is_pending',
    ];

    protected $dates = [
        'accepted_at',
        'declined_at',
    ];

    public function getIsAcceptedAttribute(): bool
    {
        return isset($this->accepted_at);
    }

    public function getIsDeclinedAttribute(): bool
    {
        return isset($this->declined_at);
    }

    public function getIsPendingAttribute(): bool
    {
        return ! $this->accepted && ! $this->declined;
    }

    public function accept()
    {
        $this->update([
            'accepted_at' => now(),
            'declined_at' => null,
        ]);
    }

    public function decline()
    {
        $this->update([
            'accepted_at' => null,
            'declined_at' => now(),
        ]);
    }
}
