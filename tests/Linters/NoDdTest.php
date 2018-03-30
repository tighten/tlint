<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\NoDd;
use Tighten\TLint;

class NoDdTest extends TestCase
{
    /** @test */
    public function catches_dd_call()
    {
        $file = <<<file
<?php

\$foo = "abc";
dd(\$foo);

file;

        $lints = (new TLint)->lint(
            new NoDd($file)
        );

        $this->assertEquals(4, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function does_not_trigger_on_dd_call_in_comments()
    {
        $file = <<<file
<?php

\$foo = "abc";
//dd(\$foo);

/**
* dd(\$foo);
*/

file;

        $lints = (new TLint)->lint(
            new NoDd($file)
        );

        $this->assertEmpty($lints);
    }
}
