<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\NoSpaceAfterBladeDirectives;
use Tighten\TLint;

class NoSpaceAfterBladeDirectivesTest extends TestCase
{
    /** @test */
    function catches_space_after_directives()
    {
        $file = <<<file
        @section ('sidebar')
            This is the master sidebar.
        @show

        <div class="container">
            @yield ('content')
        </div>
file;

        $lints = (new TLint)->lint(
            new NoSpaceAfterBladeDirectives($file)
        );

        $this->assertEquals(1, $lints[0]->getNode()->getLine());
        $this->assertEquals(6, $lints[1]->getNode()->getLine());
    }
}
