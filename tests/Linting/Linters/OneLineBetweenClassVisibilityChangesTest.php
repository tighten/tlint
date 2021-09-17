<?php

namespace Tests\Linting\Linters;

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

    /** @test */
    function catches_missing_line_between_visibility_changes_with_comment()
    {
        $file = <<<'file'
<?php

namespace App;

class Thing
{
    protected const OK = 1;
    // Note to self
    private $ok;
}
file;

        $lints = (new TLint)->lint(
            new OneLineBetweenClassVisibilityChanges($file)
        );

        $this->assertEquals(9, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_missing_line_between_visibility_changes_with_two_comments()
    {
        $file = <<<'file'
<?php

namespace App;

class Thing
{
    protected const OK = 1;
    // Note to self
    // Another
    private $ok;
}
file;

        $lints = (new TLint)->lint(
            new OneLineBetweenClassVisibilityChanges($file)
        );

        $this->assertEquals(10, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_missing_line_between_visibility_changes_with_many_comments()
    {
        $file = <<<'file'
<?php

namespace App;

class Thing
{
    protected const OK = 1;
    // Note to self
    // Another
    // And another
    // And another one
    private $ok;
}
file;

        $lints = (new TLint)->lint(
            new OneLineBetweenClassVisibilityChanges($file)
        );

        $this->assertEquals(12, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function ignores_comment_below_space_between_visibility_changes()
    {
        $file = <<<'file'
<?php

namespace App;

class Thing
{
    protected const OK = 1;

    // TODO
    private $ok;
}
file;

        $lints = (new TLint)->lint(
            new OneLineBetweenClassVisibilityChanges($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function ignores_comment_above_space_between_visibility_changes()
    {
        $file = <<<'file'
<?php

namespace App;

class Thing
{
    protected const OK = 1;
    // public const NOT_OK = 2;

    private $ok;
}
file;

        $lints = (new TLint)->lint(
            new OneLineBetweenClassVisibilityChanges($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function ignores_many_comments_between_visibility_changes()
    {
        $file = <<<'file'
<?php

namespace App;

class Thing
{
    protected const OK = 1;
    // public const NOT_OK = 2;

    // another one
    /**
     * docblock!
     */
    // Hi there
    //
    private $ok;
}
file;

        $lints = (new TLint)->lint(
            new OneLineBetweenClassVisibilityChanges($file)
        );

        $this->assertEmpty($lints);
    }
}
