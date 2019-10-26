<?php

namespace tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\Formatters\AlphabeticalImports;
use Tighten\TFormat;

class AlphabeticalImportsTest extends TestCase
{
    /** @test */
    function catches_non_alphabetical_imports()
    {
        $file = <<<file
<?php

use B\A as AA;
use A\Z\Z;

\$ok = 'thing';
file;

        $formatted = (new TFormat)->format(
            new AlphabeticalImports($file)
        );

        $correctlyFormatted = <<<file
<?php

use A\Z\Z;
use B\A as AA;

\$ok = 'thing';
file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    function catches_non_alphabetical_imports_in_namespace()
    {
        $file = <<<file
<?php

namespace Test;

use B\A as AA;
use A\Z\Z;

\$ok = 'thing';
file;

        $formatted = (new TFormat)->format(
            new AlphabeticalImports($file)
        );

        $correctlyFormatted = <<<file
<?php

namespace Test;

use A\Z\Z;
use B\A as AA;

\$ok = 'thing';
file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    function does_not_throw_when_require_is_the_first_expression()
    {
        $file = <<<file
<?php

require __DIR__ . '/vendor/autoload.php';

use PhpParser\ParserFactory;

file;

        $formatted = (new TFormat)->format(
            new AlphabeticalImports($file)
        );

        $correctlyFormatted = <<<file
<?php

require __DIR__ . '/vendor/autoload.php';

use PhpParser\ParserFactory;

file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }
}
