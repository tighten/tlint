<?php

namespace Tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Formatters\UseAuthHelperOverFacade;
use Tighten\TLint\TFormat;

class UseAuthHelperOverFacadeTest extends TestCase
{
    /** @test */
    public function catches_auth_facade_usage_in_views()
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

        $correctlyFormatted = <<<file
@extends('layouts.app')
@if (!auth()->check())
    <label for="email">Email</label>
    <input id="email" class="form-control" type="email" name="email" required>
@else
    <input id="email" type="hidden" name="email" value="{{ auth()->user()->email }}" required>
@endif
file;

        $formatted = (new TFormat)->format(
            new UseAuthHelperOverFacade($file, '.blade.php')
        );

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function catches_auth_facade_usage_in_code()
    {
        $file = <<<'file'
<?php
use Illuminate\Support\Facades\Auth;

echo Auth::user()->name;
echo Auth::user()->projects()->count();
Auth::login($user);
file;

        $correctlyFormatted = <<<'file'
<?php
use Illuminate\Support\Facades\Auth;

echo auth()->user()->name;
echo auth()->user()->projects()->count();
auth()->login($user);
file;

        $formatted = (new TFormat)->format(
            new UseAuthHelperOverFacade($file, '.php')
        );

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function does_not_trigger_on_non_auth_call()
    {
        $file = <<<file
<?php

use Some\Other\AuthClass as Auth;

echo Auth::user()->name;
file;

        $formatted = (new TFormat)->format(
            new UseAuthHelperOverFacade($file, '.php')
        );

        $this->assertEquals($file, $formatted);
    }

    /** @test */
    public function does_not_trigger_on_non_facade_call()
    {
        $file = <<<file
<?php

echo Auth::nonFacadeMethod()->value;
file;

        $formatted = (new TFormat)->format(
            new UseAuthHelperOverFacade($file, '.php')
        );

        $this->assertEquals($file, $formatted);
    }

    /** @test */
    public function does_not_trigger_when_calling_routes()
    {
        $file = <<<file
<?php

use Illuminate\Support\Facades\Auth;

Auth::routes();
file;

        $formatted = (new TFormat)->format(
            new UseAuthHelperOverFacade($file, '.php')
        );

        $this->assertEquals($file, $formatted);
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

        $formatted = (new TFormat)->format(
            new UseAuthHelperOverFacade($file, '.php')
        );

        $this->assertEquals($file, $formatted);
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

        $formatted = (new TFormat)->format(
            new UseAuthHelperOverFacade($file, '.php')
        );

        $this->assertEquals($file, $formatted);
    }

    /** @test */
    public function does_not_attempt_to_compile_x_component_tags()
    {
        $file = <<<'file'
<x-main-layout>
    Hello world!
</x-main-layout>
file;

        $formatted = (new TFormat)->format(
            new UseAuthHelperOverFacade($file, '.blade.php')
        );

        $this->assertEquals($file, $formatted);
    }
}
