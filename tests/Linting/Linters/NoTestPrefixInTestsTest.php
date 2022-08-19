<?php

namespace Tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\NoTestPrefixInTests;
use Tighten\TLint\TLint;

class NoTestPrefixInTestsTest extends TestCase
{
    /** @test */
    public function does_not_trigger_on_test_method_with_annotation()
    {
        $file = <<<file
<?php

use PHPUnit\Framework\TestCase;

class NoTestPrefixInTestsTest extends TestCase
{
    /** @test */
    function catches_test_method_with_prefix()
    {

    }
}

file;

        $lints = (new TLint)->lint(
            new NoTestPrefixInTests($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function catches_snake_case_test_method_with_test_prefix()
    {
        $file = <<<file
<?php

use PHPUnit\Framework\TestCase;

class NoTestPrefixInTestsTest extends TestCase
{
    public function test_catches_test_method_with_prefix()
    {

    }
}

file;

        $lints = (new TLint)->lint(
            new NoTestPrefixInTests($file)
        );

        $this->assertEquals(7, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function catches_camel_case_test_method_with_test_prefix()
    {
        $file = <<<file
<?php

use PHPUnit\Framework\TestCase;

class NoTestPrefixInTestsTest extends TestCase
{
    public function testCatchesTestMethodWithPrefix()
    {

    }
}

file;

        $lints = (new TLint)->lint(
            new NoTestPrefixInTests($file)
        );

        $this->assertEquals(7, $lints[0]->getNode()->getLine());
    }
}
