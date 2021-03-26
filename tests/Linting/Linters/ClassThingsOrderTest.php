<?php

namespace tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\Linters\ClassThingsOrder;
use Tighten\TLint;

class ClassThingsOrderTest extends TestCase
{
    /** @test */
    function catches_wrong_order_for_class_things()
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

    /** @test */
    function identifies_constructor_method()
    {
        $file = <<<file
<?php

namespace App;

class Thing
{

    private function __construct(\$foo)
    {
        \$foo = "bar";
    }

    public function bar()
    {
        //
    }
}
file;

        $lints = (new TLint)->lint(
            new ClassThingsOrder($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function handles_empty_class()
    {
        $file = <<<file
<?php

namespace App;

class Thing
{
    //
}
file;

        $lints = (new TLint)->lint(
            new ClassThingsOrder($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function handles_magic_methods()
    {
        $file = <<<file
<?php

namespace App;

class Thing
{
    protected function foo()
    {
        //
    }

    public function __call(\$method, \$parameters)
    {
        //
    }
}
file;

        $lints = (new TLint)->lint(
            new ClassThingsOrder($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function triggers_when_static_method_is_above_constructor()
    {
        $file = <<<file
<?php

class Test
{
    public static function test()
    {
        return true;
    }

    public function __construct()
    {

    }
}

file;

        $lints = (new TLint)->lint(
            new ClassThingsOrder($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function does_not_trigger_on_setup_setup()
    {
        $file = <<<file
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        // Do stuff
        parent::setUp();
    }

    /** @test */
    function true_is_true()
    {
        \$this->assertTrue(true);
    }
}
file;

        $lints = (new TLint)->lint(
            new ClassThingsOrder($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_on_phpunit_teardown()
    {
        $file = <<<file
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Do stuff
        parent::tearDown();
    }

    /** @test */
    function true_is_true()
    {
        \$this->assertTrue(true);
    }
}
file;

        $lints = (new TLint)->lint(
            new ClassThingsOrder($file)
        );

        $this->assertEmpty($lints);
    }
}
