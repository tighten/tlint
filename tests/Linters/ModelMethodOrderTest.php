<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\ModelMethodOrder;
use Tighten\TLint;

class ModelMethodOrderTest extends TestCase
{
    /** @test */
    function catches_wrong_order_for_model_methods()
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
    
    public function phone()
    {
        return \$this->hasOne('App\Phone');
    }
}
file;

        $lints = (new TLint)->lint(
            new ModelMethodOrder($file)
        );

        $this->assertEquals(7, $lints[0]->getNode()->getLine());
    }
}
