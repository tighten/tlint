<?php

namespace Tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\UseAuthHelperOverFacade;
use Tighten\TLint\TLint;

class UseAuthHelperOverFacadeTest extends TestCase
{
    /** @test */
    public function catches_auth_facade_usage_in_code()
    {
        $file = <<<file
<?php
use Illuminate\Support\Facades\Auth;

echo Auth::user()->name;
file;

        $lints = (new TLint)->lint(
            new UseAuthHelperOverFacade($file, '.php')
        );

        $this->assertEquals(4, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function does_not_trigger_on_non_auth_call()
    {
        $file = <<<file
    <?php

    use Some\Other\AuthClass as Auth;

    echo Auth::user()->name;
    file;

        $lints = (new TLint)->lint(
            new UseAuthHelperOverFacade($file, '.php')
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function does_not_trigger_on_non_facade_call()
    {
        $file = <<<'file'
<?php

echo Auth::nonFacadeMethod()->value;
file;

        $lints = (new TLint)->lint(
            new UseAuthHelperOverFacade($file, '.php')
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function does_not_trigger_when_calling_routes()
    {
        $file = <<<file
<?php

use Illuminate\Support\Facades\Auth;

Auth::routes();
file;

        $lints = (new TLint)->lint(
            new UseAuthHelperOverFacade($file, '.php')
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function does_not_throw_when_file_contains_dynamic_class_variables()
    {
        $file = <<<file
<?php

namespace App\Newsboard\Factory;

class Relationships
{
    static function randomOrCreate(\$className)
    {
        if (\$className::all()->count() > 0) {
            return \$className::all()->random();
        }

        return factory(\$className)->create();
    }
}
file;

        $lints = (new TLint)->lint(
            new UseAuthHelperOverFacade($file, '.php')
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function does_not_throw_when_using_static_call_on_dynamic_variable()
    {
        $file = <<<file
<?php

use Illuminate\Support\Facades\Auth;

class Test
{
    static function test(\$class)
    {
        \$class::test();
    }
}
file;

        $lints = (new TLint)->lint(
            new UseAuthHelperOverFacade($file, '.php')
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function does_not_attempt_to_compile_x_component_tags()
    {
        $file = <<<'file'
<x-main-layout>
    Hello world!
</x-main-layout>
file;

        $this->assertEmpty((new TLint)->lint(new UseAuthHelperOverFacade($file, '.blade.php')));
    }
}
