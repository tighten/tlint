<?php

namespace tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\Formatters\AlphabeticalImports;
use Tighten\TFormat;

class AlphabeticalImportsTest extends TestCase
{
    /** @test */
    function fixes_non_alphabetical_imports()
    {
        $file = <<<file
<?php

use B\\A as AA;
use A\\Z\\Z;

\$ok = 'thing';
file;

        $formatted = (new TFormat)->format(
            new AlphabeticalImports($file)
        );

        $correctlyFormatted = <<<file
<?php

use A\\Z\\Z;
use B\\A as AA;

\$ok = 'thing';
file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    function fixes_non_alphabetical_imports_in_namespace()
    {
        $file = <<<file
<?php

namespace Test;

use B\\A as AA;
use A\\Z\\Z;

\$ok = 'thing';
file;

        $formatted = (new TFormat)->format(
            new AlphabeticalImports($file)
        );

        $correctlyFormatted = <<<file
<?php

namespace Test;

use A\\Z\\Z;
use B\\A as AA;

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

use PhpParser\\ParserFactory;

file;

        $formatted = (new TFormat)->format(
            new AlphabeticalImports($file)
        );

        $correctlyFormatted = <<<file
<?php

require __DIR__ . '/vendor/autoload.php';

use PhpParser\\ParserFactory;

file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    function works_with_function_imports()
    {
        $file = <<<file
<?php

namespace tests;

use function Tighten\\version;
use function PHPUnit\\Framework\\test;

file;

        $formatted = (new TFormat)->format(
            new AlphabeticalImports($file)
        );

        $correctlyFormatted = <<<file
<?php

namespace tests;

use function PHPUnit\\Framework\\test;
use function Tighten\\version;

file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    function works_with_const_imports()
    {
        $file = <<<file
<?php

namespace tests;

use const Tighten\\VERSION;
use const PHPUnit\\Framework\\TEST;

file;

        $formatted = (new TFormat)->format(
            new AlphabeticalImports($file)
        );

        $correctlyFormatted = <<<file
<?php

namespace tests;

use const PHPUnit\\Framework\\TEST;
use const Tighten\\VERSION;

file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    function orders_import_types_by_class_function_const_with_a_line_between()
    {
        $file = <<<file
<?php

namespace tests;

use com\\test\\ClassA;
use const com\\test\\ConstA;
use function com\\test\\fn_b;

file;

        $formatted = (new TFormat)->format(
            new AlphabeticalImports($file)
        );

        $correctlyFormatted = <<<file
<?php

namespace tests;

use com\\test\\ClassA;
use function com\\test\\fn_b;
use const com\\test\\ConstA;

file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    function groups_types_of_imports_properly()
    {
        $file = <<<file
<?php

namespace tests;

use const com\\test\\ConstB;
use com\\test\\ClassB;
use const com\\test\\ConstA;
use function com\\test\\fn_b;

file;

        $formatted = (new TFormat)->format(
            new AlphabeticalImports($file)
        );

        $correctlyFormatted = <<<file
<?php

namespace tests;

use com\\test\\ClassB;
use function com\\test\\fn_b;
use const com\\test\\ConstA;
use const com\\test\\ConstB;

file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    function does_nothing_when_group_imports_are_used()
    {
        $file = <<<file
<?php

use Z;
use Symfony\\Component\\{Console\\Application, Console\\Tester\\CommandTester};

\$ok = 'thing';

file;

        $formatted = (new TFormat)->format(
            new AlphabeticalImports($file)
        );

        $correctlyFormatted = <<<file
<?php

use Z;
use Symfony\\Component\\{Console\\Application, Console\\Tester\\CommandTester};

\$ok = 'thing';

file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }
}
