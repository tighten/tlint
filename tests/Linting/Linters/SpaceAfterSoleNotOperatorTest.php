<?php

namespace tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\Linters\SpaceAfterSoleNotOperator;
use Tighten\TLint;

class SpaceAfterSoleNotOperatorTest extends TestCase
{
    /** @test */
    function catches_missing_space()
    {
        $file = <<<file
<?php

if (!\$thing) {
    echo 'test';
}
file;

        $lints = (new TLint)->lint(
            new SpaceAfterSoleNotOperator($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_missing_space_in_elseif()
    {
        $file = <<<file
<?php

if (\$thing) {
    echo 'test';
} elseif (!\$thing) {
    echo 'test';
}
file;

        $lints = (new TLint)->lint(
            new SpaceAfterSoleNotOperator($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_missing_space_in_non_primary_condition()
    {
        $file = <<<file
<?php

if (\$thing && !\$thing2) {
    echo 'test';
}
file;

        $lints = (new TLint)->lint(
            new SpaceAfterSoleNotOperator($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_missing_space_with_function_call()
    {
        $file = <<<file
<?php

if (!call_user_func(function () {})) {
    echo 'test';
}
file;

        $lints = (new TLint)->lint(
            new SpaceAfterSoleNotOperator($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function does_not_trigger_when_combined_with_other_condition_operators()
    {
        $file = <<<file
<?php

if (1 != 2) {
    echo 'test';
}
file;

        $lints = (new TLint)->lint(
            new SpaceAfterSoleNotOperator($file)
        );

        $this->assertEmpty($lints);
    }
}
