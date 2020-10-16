<?php

namespace testing\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\Linters\ConcatenationNoSpacing;
use Tighten\TLint;

class ConcatenationNoSpacingTest extends TestCase
{
    /** @test */
    function catches_concat_with_spaces()
    {
        $file = <<<'file'
<?php

echo "foo bar . " . $baz;

file;

        $lints = (new TLint)->lint(
            new ConcatenationNoSpacing($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_concat_with_space_on_one_side()
    {
        $file = <<<'file'
<?php

echo "foo bar . ". $baz;
echo "foo bar . " .$baz;

file;

        $lints = (new TLint)->lint(
            new ConcatenationNoSpacing($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
        $this->assertEquals(4, $lints[1]->getNode()->getLine());
    }

    /** @test */
    function does_not_trigger_on_concat_with_no_spaces()
    {
        $file = <<<'file'
<?php

echo "foo bar . ".$baz;

file;

        $lints = (new TLint)->lint(
            new ConcatenationNoSpacing($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function handles_valid_multiline_concat()
    {
        $file = <<<'file'
<?php

echo '! '.$this->linter->getLintDescription().PHP_EOL
                .$this->node->getLine().' : `'.$this->linter->getCodeLine($this->node->getLine()).'`';

file;

        $lints = (new TLint)->lint(
            new ConcatenationNoSpacing($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function triggers_on_multiline_concat()
    {
        $file = <<<'file'
<?php

echo getcwd() . '/'
    .$a;

file;

        $lints = (new TLint)->lint(
            new ConcatenationNoSpacing($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function triggers_on_multiline_concat_where_lines_do_not_start_with_concat()
    {
        $file = <<<'file'
<?php

       $directory = 'images/articles' . DIRECTORY_SEPARATOR .
            date('Y') . DIRECTORY_SEPARATOR .
            date('m') . DIRECTORY_SEPARATOR .
            date('d') . DIRECTORY_SEPARATOR;

file;

        $lints = (new TLint)->lint(
            new ConcatenationNoSpacing($file)
        );

        $this->assertEquals(1, count($lints));
        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }
}
