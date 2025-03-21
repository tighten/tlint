<?php

namespace Tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\NoDumpDirectives;
use Tighten\TLint\TLint;

class NoDumpDirectivesTest extends TestCase
{
    /** @test */
    public function catches_dump_directive_usage()
    {
        $file = <<<'file'
            @extends('layouts.app')

            @section('content')
                <div>
                    @dump('test')
                </div>
            @endsection
            file;

        $lints = (new TLint)->lint(
            new NoDumpDirectives($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function catches_dd_directive_usage()
    {
        $file = <<<'file'
            @extends('layouts.app')

            @section('content')
                <div>
                    @dd('test')
                </div>
            @endsection
            file;

        $lints = (new TLint)->lint(
            new NoDumpDirectives($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
    }
}
