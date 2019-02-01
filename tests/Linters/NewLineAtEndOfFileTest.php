<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\NewLineAtEndOfFile;
use Tighten\TLint;

class NewLineAtEndOfFileTest extends TestCase
{
    /** @test */
    function catches_file_without_new_line_at_end()
    {
        $file = <<<file
<?php

use B\A as AA;
use A\Z\Z;

\$ok = 'thing';
file;

        $lints = (new TLint)->lint(
            new NewLineAtEndOfFile($file)
        );

        $this->assertEquals(6, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function does_not_trigger_on_file_with_newline_at_end()
    {
        $file = <<<file
<?php

use B\A as AA;
use A\Z\Z;

\$ok = 'thing';

file;

        $lints = (new TLint)->lint(
            new NewLineAtEndOfFile($file)
        );

        $this->assertEmpty($lints);
    }
}
