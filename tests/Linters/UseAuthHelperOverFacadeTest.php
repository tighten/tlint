<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\QualifiedNamesOnlyForClassName;
use Tighten\Linters\UseAuthHelperOverFacade;
use Tighten\TLint;

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

        $lints = (new TLint)->lint(
            new UseAuthHelperOverFacade($file, '.blade.php')
        );

        $this->assertNotNull($lints[0]);
    }

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
    public function does_not_trigger_on_non_facade_call()
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
}
