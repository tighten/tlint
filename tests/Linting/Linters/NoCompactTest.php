<?php

namespace Tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\NoCompact;
use Tighten\TLint\TLint;

class NoCompactTest extends TestCase
{
    /** @test */
    function catches_compact_call()
    {
        $file = <<<file
<?php

\$foo = "abc";
compact(\$foo);

file;

        $lints = (new TLint)->lint(
            new NoCompact($file)
        );

        $this->assertEquals(4, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function does_not_trigger_on_compact_call_in_comments()
    {
        $file = <<<file
<?php

\$foo = "abc";
//compact(\$foo);

/**
* compact(\$foo);
*/

file;

        $lints = (new TLint)->lint(
            new NoCompact($file)
        );

        $this->assertEmpty($lints);
    }
}
