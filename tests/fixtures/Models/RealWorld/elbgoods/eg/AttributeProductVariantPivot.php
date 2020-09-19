<?php

namespace App\Models;

final class AttributeProductVariantPivot extends Pivot
{
    public $incrementing = true;

    protected $fillable = [];

    protected $casts = [];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function product_variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * @param string|null $value
     *
     * @return int[]|float[]|bool|float|null|null[]
     */
    public function getValueAttribute(?string $value)
    {
        if ($value === null) {
            return null;
        }

        return $this->fromJson($value);
    }

    public function setValueAttribute($value): void
    {
        if ($value === null) {
            return;
        }

        $this->attributes['value'] = $this->asJson($value);
    }

    public function getValueFormattedAttribute(): ?array
    {
        return null;
    }

    public function getValueNormalizedAttribute(): ?array
    {
        return $this->normalizeNumber($this->value);
    }

    private function normalizeNumber(?float $value): ?array
    {
        if ($value === null) {
            return null;
        }

        return $this->attribute->unit_type->normalize($value);
    }

    private function formatNumber(?float $value, ?Unit $enforcedUnit = null, ?UnitSystemEnum $unitSystem = null): ?array
    {
        if ($value === null) {
            return null;
        }

        if (! $this->attribute->unit_type) {
            return null;
        }

        if ($enforcedUnit !== null) {
            return $unit->toArray();
        }

        return $this->attribute->unit_type->format($value, $unitSystem);
    }

    private function getStateSettings(): StateSettings
    {
        return app(StateSettings::class);
    }
}
