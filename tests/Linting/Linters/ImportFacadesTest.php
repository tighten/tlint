<?php

namespace testing\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\Linters\ImportFacades;
use Tighten\TLint;

class ImportFacadesTest extends TestCase
{
    /** @test */
    function does_not_trigger_when_file_is_not_namespaced()
    {
        $file = <<<file
<?php

var_dump(Hash::make('test'));
file;

        $lints = (new TLint)->lint(
            new ImportFacades($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function catches_alias_usage_without_import()
    {
        $file = <<<file
<?php

namespace Test;

var_dump(Hash::make('test'));
file;

        $lints = (new TLint)->lint(
            new ImportFacades($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function does_not_trigger_on_facade_usage_with_import()
    {
        $file = <<<file
<?php

namespace Test;

use Illuminate\Support\Facades\Hash;

var_dump(Hash::make('test'));
file;

        $lints = (new TLint)->lint(
            new ImportFacades($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_on_facade_usage_with_grouped_import()
    {
        $file = <<<file
<?php

namespace Test;

use Illuminate\Support\Facades\{Config, Hash};

var_dump(Config::get('test'));
var_dump(Hash::make('test'));
file;

        $lints = (new TLint)->lint(
            new ImportFacades($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_throw_on_variable_class_static_calls()
    {
        $file = <<<file
<?php

namespace Test;

class Relationships
{
    static function randomOrCreate(\$className)
    {
        if (\$className::all()->count() > 0) {
            return \$className::all()->random();
        }

        return factory(\$className)->create();
    }
}
file;

        $lints = (new TLint)->lint(
            new ImportFacades($file)
        );

        $this->assertEmpty($lints);
    }
}
