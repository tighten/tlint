<?php

namespace tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\Formatters\NewLineAtEndOfFile;
use Tighten\TFormat;

class NewLineAtEndOfFileTest extends TestCase
{
    /** @test */
    function adds_new_line_at_end_of_file()
    {
        $file = <<<file
<?php

class Stuff
{
}
file;

        $formatted = (new TFormat)->format(
            new NewLineAtEndOfFile($file)
        );

        $correctlyFormatted = <<<file
<?php

class Stuff
{
}

file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

/** @test */
function doesnt_adds_new_line_at_end_of_files_with_existing_new_lines()
{
    $file = <<<file
<?php

class Stuff
{
}

file;

    $formatted = (new TFormat)->format(
        new NewLineAtEndOfFile($file)
    );

    $correctlyFormatted = <<<file
<?php

class Stuff
{
}

file;

    $this->assertEquals($correctlyFormatted, $formatted);
}
}
