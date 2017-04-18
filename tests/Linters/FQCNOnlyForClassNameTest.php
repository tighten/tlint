<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\FQCNOnlyForClassName;
use Tighten\Linters\RemoveLeadingSlashNamespaces;
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
}
