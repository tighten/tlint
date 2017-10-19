<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\SpaceAfterBladeDirectives;
use Tighten\TLint;

class SpaceAfterBladeDirectivesTest extends TestCase
{
    public function catches_missing_space_after_directives()
    {
        $file = <<<file
        @if(true)
        
        @endif
        
        @foreach(\$thing as \$things)
        
        @endforeach
file;

        $lints = (new TLint)->lint(
            new SpaceAfterBladeDirectives($file)
        );

        $this->assertEquals(1, $lints[0]->getNode()->getLine());
        $this->assertEquals(5, $lints[1]->getNode()->getLine());
    }
}
