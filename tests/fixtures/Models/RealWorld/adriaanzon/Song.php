<?php

namespace App\Models;

class Song extends Model implements HasMedia
{
    use Actionable;
    use HasTranslatableSections;
    use InteractsWithMedia;
    use Searchable;
    use Sluggable;

    protected $appends = ['slug', 'title_with_translation'];

    protected $guarded = [];

    protected $hidden = ['internal_note'];

    protected static function boot()
    {
    }

    public function culture(): BelongsTo
    {
    }

    public function tenants(): BelongsToMany
    {
    }

    public function sections(): HasMany
    {
    }

    public function scopeNotOnCurrentTenant(Builder $query): void
    {
    }

    public function scopePublished(Builder $query): void
    {
    }

    public function getTitleWithTranslationAttribute()
    {
    }

    public function loadOrderedTranslatedSections(): self
    {
    }

    public function url(): string
    {
    }

    public function registerMediaCollections(): void
    {
    }

    public function toSearchableArray(): array
    {
    }

    public function shouldBeSearchable(): bool
    {
    }
}
