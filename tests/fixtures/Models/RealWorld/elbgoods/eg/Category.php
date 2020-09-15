<?php

namespace App\Models;

final class Category extends Model
{
    public $fillable = [];

    public $casts = [];

    public static function booted(): void
    {
        self::deleting(function (self $category): ?bool {});
    }

    public function parent_category(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_category_id', 'id');
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(self::class, 'parent_category_id')
            ->orderBy('position', 'asc');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class)
            ->using(AttributeCategoryPivot::class)
            ->as('attribute_category')
            ->withPivot(['id', 'position', 'origin_category_id'])
            ->orderBy('attribute_category.position');
    }

    public function attribute_groups(): BelongsToMany
    {
        return $this->belongsToMany(AttributeGroup::class)
            ->using(AttributeGroupCategoryPivot::class)
            ->as('attribute_group_category')
            ->withPivot(['id', 'position', 'origin_category_id'])
            ->orderBy('attribute_group_category.position');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->using(CategoryProductPivot::class)
            ->as('category_product')
            ->withPivot(['id']);
    }

    public function isSubcategoryOf(self $category): bool
    {
        if ($this->parent_category_id === null) {
            return false;
        }

        if ($category->getKey() === $this->parent_category_id) {
            return true;
        }

        return $this->parent_category->isSubcategoryOf($category);
    }

    public function newCollection(array $models = []): CategoryCollection
    {
        return new CategoryCollection($models);
    }
}
