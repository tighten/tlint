<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\NoMethodVisibilityInTests;
use Tighten\TLint;

class NoMethodVisibilityInTestsTest extends TestCase
{
    /** @test */
    function does_not_trigger_on_test_method_without_explicit_visibility()
    {
        $file = <<<file
<?php

class NoMethodVisibilityInTestsTest extends TestCase
{
    /** @test */
    function catches_test_method_with_visibility()
    {

    }
}

file;

        $lints = (new TLint)->lint(
            new NoMethodVisibilityInTests($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function catches_test_method_with_public_visibility()
    {
        $file = <<<file
<?php

class NoMethodVisibilityInTestsTest extends TestCase
{
    /** @test */
    public function catches_test_method_with_visibility()
    {

    }
}

file;

        $lints = (new TLint)->lint(
            new NoMethodVisibilityInTests($file)
        );

        $this->assertEquals(6, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_test_method_with_protected_visibility()
    {
        $file = <<<file
<?php

class NoMethodVisibilityInTestsTest extends TestCase
{
    /** @test */
    protected function catches_test_method_with_visibility()
    {

    }
}

file;

        $lints = (new TLint)->lint(
            new NoMethodVisibilityInTests($file)
        );

        $this->assertEquals(6, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_test_method_with_private_visibility()
    {
        $file = <<<file
<?php

class NoMethodVisibilityInTestsTest extends TestCase
{
    /** @test */
    private function catches_test_method_with_visibility()
    {

    }
}

file;

        $lints = (new TLint)->lint(
            new NoMethodVisibilityInTests($file)
        );

        $this->assertEquals(6, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_static_test_method_with_public_visibility()
    {
        $file = <<<file
<?php

class NoMethodVisibilityInTestsTest extends TestCase
{
    /** @test */
    public static function setUp()
    {

    }
}

file;

        $lints = (new TLint)->lint(
            new NoMethodVisibilityInTests($file)
        );

        $this->assertEquals(6, $lints[0]->getNode()->getLine());
    }
}
