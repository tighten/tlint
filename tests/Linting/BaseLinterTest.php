<?php

namespace Tests\Linting;

use LogicException;
use PHPUnit\Framework\TestCase;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\TLint;

class BaseLinterTest extends TestCase
{
    /** @test */
    public function throw_exception_if_neither_lint_or_visitor_methods_are_overridden()
    {
        $linter = new class('') extends BaseLinter
        {
            public const DESCRIPTION = 'Test linter';
        };

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Custom linters must override either the `lint` or `visitor` method.');

        (new TLint)->lint($linter);
    }
}
