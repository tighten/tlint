<?php

namespace Tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\NoJsonDirective;
use Tighten\TLint\TLint;

class NoJsonDirectiveTest extends TestCase
{
    /** @test */
    public function catches_json_directive_usage()
    {
        $file = <<<'file'
        @extends('layouts.app')

        @section('content')
            <div>
                <test :files='@json([['name' => "Logan's thing"], ["name" => "ok"]])"></test>
            </div>
        @endsection

file;

        $lints = (new TLint())->lint(
            new NoJsonDirective($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
    }
}
