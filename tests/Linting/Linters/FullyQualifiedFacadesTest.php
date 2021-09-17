<?php

namespace Tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\FullyQualifiedFacades;
use Tighten\TLint\TLint;

class FullyQualifiedFacadesTest extends TestCase
{
    /** @test */
    public function it_triggers_on_namespaced_file()
    {
        $file = <<<file
<?php

namespace Test;

use Cache;

file;

        $lints = (new TLint)->lint(
            new FullyQualifiedFacades($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function it_triggers_on_non_namespaced_file()
    {
        $file = <<<file
<?php

use Cache;

file;

        $lints = (new TLint)->lint(
            new FullyQualifiedFacades($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function it_triggers_on_aliased_facade()
    {
        $file = <<<file
<?php

use Cache as Store;

file;

        $lints = (new TLint)->lint(
            new FullyQualifiedFacades($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function does_not_trigger_on_a_fully_qualified_facade()
    {
        $file = <<<file
<?php

namespace Test;

use Illuminate\Support\Facades\Hash;

file;

        $lints = (new TLint)->lint(
            new FullyQualifiedFacades($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function does_not_trigger_on_custom_class_aliased_to_facade_name()
    {
        $file = <<<file
<?php

namespace Test;

use MyNamespace\MyClass as Config;

file;

        $lints = (new TLint)->lint(
            new FullyQualifiedFacades($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function does_not_trigger_on_facade_usage_with_grouped_import()
    {
        $file = <<<file
<?php

namespace Test;

use Illuminate\Support\Facades\{Config, Hash};

file;

        $lints = (new TLint)->lint(
            new FullyQualifiedFacades($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function does_not_trigger_on_facade_usage_with_grouped_import_and_renamed_imports()
    {
        $file = <<<file
<?php

namespace Test;

use Illuminate\Support\Facades\{Config, Hash};
use Some\Othere\Namespace\{Config as OtherConfig};
use Some\Other\Namespace\File;

file;

        $lints = (new TLint)->lint(
            new FullyQualifiedFacades($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function ignores_unknown_aliases_in_namespaced_file()
    {
        $file = <<<'file'
<?php

namespace Test;

use Shortcut;

file;

        $lints = (new TLint)->lint(
            new FullyQualifiedFacades($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function ignores_unknown_aliases_in_non_namespaced_file()
    {
        $file = <<<'file'
<?php

use Shortcut;

file;

        $lints = (new TLint)->lint(
            new FullyQualifiedFacades($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function ignores_files_in_same_directory_with_same_name_as_facade_alias()
    {
        $file = <<<'file'
<?php

namespace App\Utilities;

class Stuff
{
    public function doStuff()
    {
        return File::otherStuff();
    }
}
file;

        $lints = (new TLint)->lint(
            new FullyQualifiedFacades($file)
        );

        $this->assertEmpty($lints);
    }
}
