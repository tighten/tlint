<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Thing extends Model
{
    public static function booted()
    {

    }

    public static function boot()
    {

    }

    public static function booting()
    {

    }

    public static function make(array $attributes)
    {

    }

    protected static function makeInternal(array $attributes)
    {

    }

    public function scopeWhereInactive(Builder $query)
    {
        return $query->where('is_active', false);
    }

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = strtolower($value);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo('App\Category');
    }

    public function scopeWhereActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['first_name'] = strtolower($value);
    }

    protected function publish()
    {

    }

    public function activate()
    {
        $this->is_active = true;
    }

    public function phone(): HasOne
    {
        return $this->hasOne('App\Phone');
    }

    public function images(): MorphMany
    {
        return $this->media()->where('type', 'image');
    }

    public function media()
    {
        return $this->morphMany('App\Media');
    }

    public function comments()
    {
        $model = 'App\Comment';
        return $this->morphMany($model);
    }

    public function tags(): HasMany
    {
        return $this->hasMany('App\Tag');
    }

    public function getFirstNameAttribute($value)
    {
        return ucfirst($value);
    }
}
