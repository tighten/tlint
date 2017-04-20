<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\FQCNOnlyForClassName;
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
echo Auth::user()->name;
file;

        $lints = (new TLint)->lint(
            new UseAuthHelperOverFacade($file, '.php')
        );

        $this->assertEquals(2, $lints[0]->getNode()->getLine());
    }
}
