<?php

namespace App\Models;

// @todo: https://github.com/tighten/tlint/pull/169
final class ProductVariant extends Model
{
    protected $fillable = [];
    protected $casts = [];

    public static function booted(): void
    {
        self::deleting(function (self $productVariant): void {});

        self::deleted(function (self $productVariant): void {});
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class)
            ->using(AttributeProductVariantPivot::class)
            ->as('attribute_product_variant')
            ->withPivot(['id', 'value']);
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class)
            ->using(MediaProductVariantPivot::class)
            ->as('media_product_variant')
            ->withPivot(['position'])
            ->orderBy('media_product_variant.position');
    }

    public function product_variant_code(): HasOne
    {
        return $this->hasOne(ProductVariantCode::class);
    }

    public function getNameAttribute(): array
    {
        return collect(config('app.locales'))
            ->mapWithKeys(function (): array {
                return [];
            })
            ->all();
    }

    public function getFullNameAttribute(): array
    {
        return array_map(
            function () {
                return '';
            },
            $this->name
        );
    }

    public function getPreviewImageAttribute(): ?Media
    {
        return $this->media()->first();
    }

    public function clone(): self
    {
        /** @var ProductVariant $clone */
        $clone = $this->replicate();

        return $clone;
    }

    public function searchableIndex(): self
    {
        return $this;
    }

    public function searchableDelete(): self
    {
        return $this;
    }
}
