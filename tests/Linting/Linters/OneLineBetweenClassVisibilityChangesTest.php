<?php

namespace tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\OneLineBetweenClassVisibilityChanges;
use Tighten\TLint\TLint;

class OneLineBetweenClassVisibilityChangesTest extends TestCase
{
    /** @test */
    function catches_missing_line_between_visibility_changes()
    {
        $file = <<<file
<?php

namespace App;

class Thing
{
    protected const OK = 1;
    private \$ok;
}
file;

        $lints = (new TLint)->lint(
            new OneLineBetweenClassVisibilityChanges($file)
        );

        $this->assertEquals(8, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_missing_line_between_visibility_changes_with_doc_block()
    {
        $file = <<<file
<?php

namespace App;

class Thing
{
    protected const OK = 1;
    /**
     * The description of something.
     */
    private \$ok;
}
file;

        $lints = (new TLint)->lint(
            new OneLineBetweenClassVisibilityChanges($file)
        );

        $this->assertEquals(11, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function ignores_doc_block_between_visibility_changes()
    {
        $file = <<<file
<?php

namespace App;

class Thing
{
    protected const OK = 1;

    /**
     * The description of something.
     */
    private \$ok;
}
file;

        $lints = (new TLint)->lint(
            new OneLineBetweenClassVisibilityChanges($file)
        );

        $this->assertEmpty($lints);
    }
}
