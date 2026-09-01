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

        $lints = (new TLint)->lint(
            new NoJsonDirective($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
        $this->assertStringContainsString(
            '{!! json_encode($var, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}',
            (string) $lints[0]
        );
    }
}
