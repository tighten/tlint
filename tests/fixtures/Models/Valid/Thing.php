<?php

namespace App;

class Thing extends Model
{
    use Notifiable;

    protected $fillable = [];
    protected $guarded = [];
    protected $hidden = [];
    protected $casts = [];
    protected $dates = [];

    public static function booting()
    {
    }

    public static function boot()
    {
    }

    public static function booted()
    {
    }

    public static function fromDraft()
    {
    }

    protected static function toDraft()
    {
    }

    public function author(): BelongsTo
    {
    }

    public function scopeWhereIsPublished()
    {
    }

    public function getPasswordAttribute()
    {
    }

    public function setPasswordAttribute()
    {
    }

    public function publish()
    {
    }

    public function unpublish()
    {
    }

    protected function drafting()
    {
    }
}
