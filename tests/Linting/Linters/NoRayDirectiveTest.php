<?php

namespace Tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\NoRayDirective;
use Tighten\TLint\TLint;

class NoRayDirectiveTest extends TestCase
{
    /** @test */
    public function catches_ray_directive_usage()
    {
        $file = <<<'file'
            @extends('layouts.app')

            @section('content')
                <div>
                    @ray('test')
                </div>
            @endsection
            file;

        $lints = (new TLint)->lint(
            new NoRayDirective($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
    }
}
