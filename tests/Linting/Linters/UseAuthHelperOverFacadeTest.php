<?php

namespace testing\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\Linters\UseAuthHelperOverFacade;
use Tighten\TLint;

class UseAuthHelperOverFacadeTest extends TestCase
{
    /** @test */
    function catches_auth_facade_usage_in_views()
    {
        $file = <<<file
   @extends('layouts.app')
   @if (!\Illuminate\Support\Facades\Auth::check())
        <label for="email">Email</label>
        <input id="email" class="form-control" type="email" name="email" required>
    @else
        <input id="email" type="hidden" name="email" value="{{ auth()->user()->email }}" required>
    @endif
file;

        $lints = (new TLint)->lint(
            new UseAuthHelperOverFacade($file, '.blade.php')
        );

        $this->assertNotNull($lints[0]);
    }

    /** @test */
    function catches_auth_facade_usage_in_code()
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
    function does_not_trigger_on_non_facade_call()
    {
        $file = <<<file
<?php

echo Auth::nonFacadeMethod()->value;
file;

        $lints = (new TLint)->lint(
            new UseAuthHelperOverFacade($file, '.php')
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_when_calling_routes()
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
    function does_not_throw_when_file_contains_dynamic_class_variables()
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
    function does_not_throw_when_using_static_call_on_dynamic_variable()
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
}
