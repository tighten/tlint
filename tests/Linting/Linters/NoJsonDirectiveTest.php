<?php

namespace tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\Linters\NoJsonDirective;
use Tighten\TLint;

class NoJsonDirectiveTest extends TestCase
{
    /** @test */
    function catches_json_directive_usage()
    {
        $file = <<<file
        @extends('layouts.app')

        @section('content')
            <div>
                <test :files='@json([['name' => "Logan's thing"], ["name" => "ok"]])"></test>
            </div>
        @endsection

file;

        $lints = (new TLint)->lint(
            new NoJsonDirective($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
    }
}
