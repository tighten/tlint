<?php

namespace testing\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\Linters\ModelMethodOrder;
use Tighten\TLint;

class ModelMethodOrderTest extends TestCase
{
    /** @test */
    function catches_wrong_order_for_model_methods()
    {
        $file = <<<PHP
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

    public static function booting()
    {

    }

    public static function boot()
    {

    }

    public function scopeWhereInactive(Builder \$query)
    {
        return \$query->where('is_active', false);
    }

    public function setFirstNameAttribute(\$value)
    {
        \$this->attributes['first_name'] = strtolower(\$value);
    }

    public function category(): BelongsTo
    {
        return \$this->belongsTo('App\Category');
    }

    public function scopeWhereActive(Builder \$query)
    {
        return \$query->where('is_active', true);
    }

    public function setLastNameAttribute(\$value)
    {
        \$this->attributes['first_name'] = strtolower(\$value);
    }

    public function activate()
    {
        \$this->is_active = true;
    }

    public function phone(): HasOne
    {
        return \$this->hasOne('App\Phone');
    }

    public function images(): MorphMany
    {
        return \$this->media()->where('type', 'image');
    }

    public function media()
    {
        return \$this->morphMany('App\Media');
    }

    public function comments()
    {
        \$model = 'App\Comment';
        return \$this->morphMany(\$model);
    }

    public function tags(): HasMany
    {
        return \$this->hasMany('App\Tag');
    }

    public function getFirstNameAttribute(\$value)
    {
        return ucfirst(\$value);
    }
}
PHP;

        $lints = (new TLint)->lint(
            new ModelMethodOrder($file)
        );

        $this->assertInstanceOf(ModelMethodOrder::class, $lints[0]->getLinter());

        $this->assertEquals(implode(PHP_EOL, [
            'Model method order should be: relationships > scopes > accessors > mutators > booting > boot > booted > custom',
            'Methods are expected to be ordered like:',
            ' * category() is matched as "relationship"',
            ' * phone() is matched as "relationship"',
            ' * images() is matched as "relationship"',
            ' * media() is matched as "relationship"',
            ' * comments() is matched as "relationship"',
            ' * tags() is matched as "relationship"',
            ' * scopeWhereInactive() is matched as "scope"',
            ' * scopeWhereActive() is matched as "scope"',
            ' * getFirstNameAttribute() is matched as "accessor"',
            ' * setFirstNameAttribute() is matched as "mutator"',
            ' * setLastNameAttribute() is matched as "mutator"',
            ' * booting() is matched as "booting"',
            ' * boot() is matched as "boot"',
            ' * booted() is matched as "booted"',
            ' * activate() is matched as "custom"',
        ]), $lints[0]->getLinter()->getLintDescription());

        $this->assertEquals(11, $lints[0]->getNode()->getLine());
    }
}
