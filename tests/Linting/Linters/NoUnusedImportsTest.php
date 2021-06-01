<?php

namespace Tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\NoUnusedImports;
use Tighten\TLint\TLint;

class NoUnusedImportsTest extends TestCase
{
    /** @test */
    function catches_unused_import()
    {
        $file = <<<file
<?php

use Test\ThingA;
use Test\ThingB;

\$testA = new ThingB;
\$testB = ThingB::class;
\$testC = ThingB::make();

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function does_not_trigger_when_import_used_to_extend_class()
    {
        $file = <<<file
<?php

use Test\ThingA;

class ThingC extends ThingA
{

}

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_when_import_used_as_typehint()
    {
        $file = <<<file
<?php

use Test\ThingA;

\$func = function (ThingA \$thingA) {
    return 1;
};

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_when_import_used_as_typehint_in_catch()
    {
        $file = <<<file
<?php

use Test\ThingA;

try {

} catch (ThingA \$e) {

}

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_when_import_used_in_instanceof_check()
    {
        $file = <<<file
<?php

use Test\ThingA;

if (\$thing instanceof ThingA) {

}

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_when_import_used_in_trait_use()
    {
        $file = <<<file
<?php

use Test\ThingA;

class Thing
{
    use ThingA;
    use ThingB;
}

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_when_import_is_callable()
    {
        $file = <<<file
<?php

use Closure;

Closure::fromCallable([\$test, 'isTraitUse']);

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_when_import_is_aliased()
    {
        $file = <<<file
<?php

use Test\ThingA as ThingB;

\$testA = new ThingB;
\$testB = ThingB::class;
\$testC = ThingB::make();

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function handles_variable_class_static_const()
    {
        $file = <<<file
<?php

\$var::do();

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function catches_unused_import_handles_variable_class_instantiation()
    {
        $file = <<<file
<?php

use Test\ThingA;
use Test\ThingB;

\$testA = new ThingB;
\$testB = ThingB::class;
\$testC = new \$testB;

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function does_not_trigger_when_import_is_used_as_an_interface()
    {
        $file = <<<file
<?php

use Test\ThingA;

class Thing implements ThingA
{
}

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_on_interface_import()
    {
        $file = <<<file
<?php

use Test\ThingA;

interface Thing extends ThingA
{
}

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_when_import_is_used_as_an_interface_along_with_extends()
    {
        $file = <<<file
<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;

class CancelUserForBadCredentials extends Job implements ShouldQueue
{
}

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_on_imported_functions()
    {
        $file = <<<file
<?php

use function Tighten\TLint\afunction;

afunction();

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_when_used_in_class_method_return_typehint()
    {
        $file = <<<file
<?php

use App\Job;

class Test
{
    public function test() : Job
    {
        //
    }
}

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_when_used_in_class_method_return_nullable_typehint()
    {
        $file = <<<file
<?php

use App\User;

class Test
{
    public function test() : ?User
    {
        //
    }
}

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_when_used_in_function_return_typehint()
    {
        $file = <<<file
<?php

use App\Job;

function test() : Job
{
    //
}

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_when_used_in_function_return_nullable_typehint()
    {
        $file = <<<file
<?php

use App\User;

function test() : ?User
{
    //
}

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_when_used_in_static_property_fetch()
    {
        $file = <<<file
<?php

use App\Job;

function test()
{
    echo Job::TYPES;
}

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function handles_instanceof_for_dynamic_expressions()
    {
        $file = <<<file
<?php

class Test
{
    function test(\$a, \$b)
    {
        return \$a instanceof \$b;
    }
}
file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function handles_nullable_parameters()
    {
        $file = <<<file
<?php

use App\Job;

class Test
{
    function test(?Job \$j)
    {
        return \$j ?? false;
    }
}
file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_on_partial_use_statements()
    {
        $file = <<<file
<?php

use Tighten\TLint\Linters;

class TightenPreset implements PresetInterface
{
    public function getLinters() : array
    {
        return [
            Linters\AlphabeticalImports::class,
            Linters\ApplyMiddlewareInRoutes::class,
        ];
    }
}

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_when_used_in_class_property_typehint()
    {
        $file = <<<file
<?php

use App\Job;

class Test
{
    public Job \$job;
}

file;

        $lints = (new TLint)->lint(
            new NoUnusedImports($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_when_import_used_in_union_type_function_parameter_typehint()
    {
        $file = <<<'file'
<?php

use App\Job;

function test(string|Job $j)
{
    return $j ?? false;
}
file;

        $this->assertEmpty((new TLint)->lint(new NoUnusedImports($file)));
    }

    /** @test */
    function does_not_trigger_when_import_used_in_union_type_method_parameter_typehint()
    {
        $file = <<<'file'
<?php

use App\AnotherJob;
use App\Job;

class Test
{
    function test(AnotherJob|Job $j)
    {
        return $j ?? false;
    }
}
file;

        $this->assertEmpty((new TLint)->lint(new NoUnusedImports($file)));
    }

    /** @test */
    function does_not_trigger_when_import_used_in_union_type_method_return_typehint()
    {
        $file = <<<'file'
<?php

use App\Job;

class Test
{
    public function test(): boolean|Job
    {
        //
    }
}
file;

        $this->assertEmpty((new TLint)->lint(new NoUnusedImports($file)));
    }

    /** @test */
    function does_not_trigger_when_import_used_in_union_type_function_return_typehint()
    {
        $file = <<<'file'
<?php

use App\Job;

function test(): string|Job
{
    //
}
file;

        $this->assertEmpty((new TLint)->lint(new NoUnusedImports($file)));
    }

    /** @test */
    function does_not_trigger_when_import_used_in_union_type_class_property_typehint()
    {
        $file = <<<'file'
<?php

use DateTimeInterface;

class Test
{
    public string|DateTimeInterface $start;
}
file;

        $this->assertEmpty((new TLint)->lint(new NoUnusedImports($file)));
    }

    /** @test */
    function does_not_trigger_when_import_uses_attribute()
    {
        $file = <<<'file'
<?php

use We\Are\Strict;
use Attribute\On\Property\One;
use Attribute\On\Property\Two;
use Attribute\On\Property\Three;
use Atrribute\On\Method;

#[Strict]
class Test
{
    #[One,Two]
    public string $foo;
    #[Three]
    public string $bar;
    
    #[Method]
    public function doIt(): void {
    
    }
}
file;

        $this->assertEmpty((new TLint)->lint(new NoUnusedImports($file)));
    }


}

