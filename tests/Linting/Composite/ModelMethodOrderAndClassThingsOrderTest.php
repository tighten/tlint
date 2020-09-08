<?php

namespace tests\Linting\Composite;

use PHPUnit\Framework\TestCase;
use Tighten\Linters\ClassThingsOrder;
use Tighten\Linters\ModelMethodOrder;
use Tighten\TLint;

class ModelMethodOrderAndClassThingsOrderTest extends TestCase
{
    /** @test */
    public function a_valid_sorted_model_passes_both_linters(): void
    {
        $file = <<<PHP
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
PHP;

        $lints = [];
        $lints += (new TLint)->lint(
            new ModelMethodOrder($file),
        );
        $lints += (new TLint)->lint(
            new ClassThingsOrder($file),
        );

        $this->assertEmpty($lints);
    }
}
