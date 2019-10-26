<?php

namespace testing\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\Linters\QualifiedNamesOnlyForClassName;
use Tighten\TLint;

class QualifiedNamesOnlyForClassNameTest extends TestCase
{
    /** @test */
    function catches_qualified_class_constant_calls()
    {
        $file = <<<file
<?php

var_dump(Thing\Things::const);
file;

        $lints = (new TLint)->lint(
            new QualifiedNamesOnlyForClassName($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_qualified_static_property_access()
    {
        $file = <<<file
<?php

var_dump(Thing\Things::\$thing);
file;

        $lints = (new TLint)->lint(
            new QualifiedNamesOnlyForClassName($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_qualified_static_method_calls()
    {
        $file = <<<file
<?php

var_dump(Thing\Things::get());
file;

        $lints = (new TLint)->lint(
            new QualifiedNamesOnlyForClassName($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function allows_qualified_class_name_access()
    {
        $file = <<<file
<?php

var_dump(Thing\Things::class);
file;

        $lints = (new TLint)->lint(
            new QualifiedNamesOnlyForClassName($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function catches_fully_qualified_instantiations()
    {
        $file = <<<file
<?php

echo new Thing\Thing();
file;

        $lints = (new TLint)->lint(
            new QualifiedNamesOnlyForClassName($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function does_not_triggen_on_variable_class_instantiation()
    {
        $file = <<<file
<?php

\$thing = 'OK::class';
echo new \$thing;
file;

        $lints = (new TLint)->lint(
            new QualifiedNamesOnlyForClassName($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_on_anonymous_class()
    {
        $file = <<<file
<?php

var_dump(new class () {});
file;

        $lints = (new TLint)->lint(
            new QualifiedNamesOnlyForClassName($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function catches_extends_fqcn()
    {
        $file = <<<file
<?php

        class ImportFacades extends \Tighten\BaseLinter
        {
            
        }
file;

        $lints = (new TLint)->lint(
            new QualifiedNamesOnlyForClassName($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_extends_fqcn_no_leading_slash()
    {
        $file = <<<file
<?php

        class ImportFacades extends Tighten\BaseLinter
        {
            
        }
file;

        $lints = (new TLint)->lint(
            new QualifiedNamesOnlyForClassName($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_trait_qualified()
    {
        $file = <<<file
<?php

        class ImportFacades
        {
            use Tighten\BaseLinter;
        }
file;

        $lints = (new TLint)->lint(
            new QualifiedNamesOnlyForClassName($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_trait_fully_qualified()
    {
        $file = <<<file
<?php

        class ImportFacades
        {
            use \Tighten\BaseLinter;
        }
file;

        $lints = (new TLint)->lint(
            new QualifiedNamesOnlyForClassName($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function does_not_throw_on_dynamic_class_instantiation()
    {
        $file = <<<file
<?php

namespace App\Http\Controllers\Webhooks;

class Stripe extends Controller
{
    public function dispatchStripeEvent(Request \$request)
    {
        return (new \$this->dispatchers[\$eventType])->dispatch(\$request);
    }
}
file;

        $lints = (new TLint)->lint(
            new QualifiedNamesOnlyForClassName($file)
        );

        $this->assertEmpty($lints);
    }
}
