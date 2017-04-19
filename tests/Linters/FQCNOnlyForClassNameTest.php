<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\FQCNOnlyForClassName;
use Tighten\TLint;

class FQCNOnlyForClassNameTest extends TestCase
{
    /** @test */
    public function catches_qualified_class_constant_calls()
    {
        $file = <<<file
<?php

var_dump(Thing\Things::const);
file;

        $lints = (new TLint)->lint(
            new FQCNOnlyForClassName($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function catches_qualified_static_property_access()
    {
        $file = <<<file
<?php

var_dump(Thing\Things::\$thing);
file;

        $lints = (new TLint)->lint(
            new FQCNOnlyForClassName($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function catches_qualified_static_method_calls()
    {
        $file = <<<file
<?php

var_dump(Thing\Things::get());
file;

        $lints = (new TLint)->lint(
            new FQCNOnlyForClassName($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function allows_qualified_class_name_access()
    {
        $file = <<<file
<?php

var_dump(Thing\Things::class);
file;

        $lints = (new TLint)->lint(
            new FQCNOnlyForClassName($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function catches_fully_qualified_instantiations()
    {
        $file = <<<file
<?php

echo new Thing\Thing();
file;

        $lints = (new TLint)->lint(
            new FQCNOnlyForClassName($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function does_not_triggen_on_variable_class_instantiation()
    {
        $file = <<<file
<?php

\$thing = 'OK::class';
echo new \$thing;
file;

        $lints = (new TLint)->lint(
            new FQCNOnlyForClassName($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function does_not_trigger_on_anonymous_class()
    {
        $file = <<<file
<?php

var_dump(new class () {});
file;

        $lints = (new TLint)->lint(
            new FQCNOnlyForClassName($file)
        );

        $this->assertEmpty($lints);
    }
}
