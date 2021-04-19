<?php

namespace Tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Formatters\UnusedImports;
use Tighten\TLint\TFormat;

class UnusedImportsTest extends TestCase
{
    /** @test */
    function removes_unused_imports()
    {
        $file = <<<file
<?php

use B\\A as AA;
use A\\Z\\Z;

\$ok = 'thing';
file;

        $formatted = (new TFormat)->format(
            new UnusedImports($file)
        );

        $correctlyFormatted = <<<file
<?php


\$ok = 'thing';
file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    function does_not_remove_group_imports()
    {
        $file = <<<file
<?php

use D;
use B\\A\\{BB, AA};
use C;

\$ok = 'test';
file;

        $formatted = (new TFormat)->format(
            new UnusedImports($file)
        );

        $correctlyFormatted = <<<file
<?php

use B\\A\\{BB, AA};

\$ok = 'test';
file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }
}
