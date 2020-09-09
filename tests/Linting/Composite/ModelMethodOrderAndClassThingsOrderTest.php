<?php

namespace tests\Linting\Composite;

use PHPUnit\Framework\TestCase;
use Tighten\Linters\ClassThingsOrder;
use Tighten\Linters\ModelMethodOrder;
use Tighten\TLint;

class ModelMethodOrderAndClassThingsOrderTest extends TestCase
{
    /**
     * @test
     * @dataProvider models
     */
    public function a_valid_sorted_model_passes_both_linters(string $file): void
    {
        $lints = [];
        $lints += (new TLint)->lint(
            new ModelMethodOrder($file),
        );
        $lints += (new TLint)->lint(
            new ClassThingsOrder($file),
        );

        $this->assertEmpty($lints);
    }

    public function models(): array
    {
        return [
            [
                <<<PHP
<?php

namespace App;

class Post extends Model
{
    use Notifiable;

    protected \$fillable = [];
    protected \$guarded = [];
    protected \$hidden = [];
    protected \$casts = [];
    protected \$dates = [];

    public static function booting(){}

    public static function boot(){}

    public static function booted(){}

    public static function fromDraft(){}

    protected static function toDraft(){}

    public function author(): BelongsTo {}

    public function scopeWhereIsPublished(){}

    public function getPasswordAttribute(){}

    public function setPasswordAttribute(){}

    public function publish(){}

    public function unpublish(){}

    protected function drafting(){}
}
PHP
            ],
            [
                <<<PHP
<?php

namespace App\Models;

class Song extends Model implements HasMedia
{
    use Actionable;
    use HasTranslatableSections;
    use InteractsWithMedia;
    use Searchable;
    use Sluggable;

    protected \$appends = ['slug', 'title_with_translation'];

    protected \$guarded = [];

    protected \$hidden = ['internal_note'];

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

    public function scopeNotOnCurrentTenant(Builder \$query): void
    {
    }

    public function scopePublished(Builder \$query): void
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
PHP
            ],
        ];
    }
}
