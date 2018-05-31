<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\ClassThingsOrder;
use Tighten\TLint;

class ClassThingsOrderTest extends TestCase
{
    /** @test */
    public function catches_wrong_order_for_class_things()
    {
        $file = <<<file
<?php

namespace App;

class Thing
{
    protected const OK = 1;
    private \$ok;
    
    use Thing;
    
    private static function boot()
    {
     
    }
    
    protected function setFirstNameAttribute(\$value)
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
            new ClassThingsOrder($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
    }
}
