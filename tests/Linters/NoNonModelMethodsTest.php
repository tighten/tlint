<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\NoNonModelMethods;
use Tighten\TLint;

class NoNonModelMethodsTest extends TestCase
{
    /** @test */
    public function catches_non_model_methods()
    {
        $file = <<<file
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Thing extends Model
{
    public static function boot()
    {
     
    }
    
    public function setFirstNameAttribute(\$value)
    {
        \$this->attributes['first_name'] = strtolower(\$value);
    }
    
    public function setLastNameAttribute(\$value)
    {
        \$this->attributes['first_name'] = strtolower(\$value);
    }
    
    public function getFirstNameAttribute(\$value)
    {
        return ucfirst(\$value);
    }
    
    public function getSomeRandomThing()
    {
    
    }
    
    public function phone()
    {
        return \$this->hasOne('App\Phone');
    }
    
    public function doSomeRandomThing()
    {
    
    }
}
file;

        $lints = (new TLint)->lint(
            new NoNonModelMethods($file)
        );

        $this->assertEquals(7, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function does_not_catch_relations_with_chained_calls()
    {
        $file = <<<file
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Thing extends Model
{
     public function providers()
    {
        return \$this->belongsToMany(Provider::class, 'providerResources', 'resource_id', 'provider_id');
    }
    
    public function types()
    {
        return \$this->belongsToMany(ResourceType::class, 'resourceMeta', 'resource_id', 'meta_value')
                    ->wherePivot('meta_key', 'type')
                    ->withTimestamps();
    }
    
    public function permissions()
    {
        return \$this->belongsToMany(Permission::class, 'permissionAssignments')->withTimestamps();
    }
}
file;

        $lints = (new TLint)->lint(
            new NoNonModelMethods($file)
        );

        $this->assertEmpty($lints);
    }
}
