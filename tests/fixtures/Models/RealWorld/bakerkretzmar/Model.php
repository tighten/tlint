<?php

namespace App;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

// @todo: https://github.com/tighten/tlint/issues/175
class Model extends BaseModel
{
    use SoftDeletes,
        Concerns\Translatable,
        Concerns\Uuidable;

    protected $guarded = [];

    public function updateQuietly(array $attributes = [])
    {
        return static::withoutEvents(function () use ($attributes) {
            return $this->update($attributes);
        });
    }

    // Laravel <= 6.x behaviour
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
