<?php

namespace Tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\ConcatenationSpacing;
use Tighten\TLint\TLint;

class ConcatenationSpacingTest extends TestCase
{
    /** @test */
    public function catches_concat_without_spaces()
    {
        $file = <<<file
<?php

echo "foo bar . ".\$baz;

file;

        $lints = (new TLint)->lint(
            new ConcatenationSpacing($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function catches_concat_with_too_many_spaces()
    {
        $file = <<<file
<?php

echo "foo bar . "  . \$baz;

file;

        $lints = (new TLint)->lint(
            new ConcatenationSpacing($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function catches_concat_with_space_on_one_side()
    {
        $file = <<<file
<?php

echo "foo bar . ". \$baz;
echo "foo bar . " .\$baz;

file;

        $lints = (new TLint)->lint(
            new ConcatenationSpacing($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
        $this->assertEquals(4, $lints[1]->getNode()->getLine());
    }

    /** @test */
    public function does_not_trigger_on_concat_with_spaces()
    {
        $file = <<<file
<?php

echo "foo bar . " . \$baz;

file;

        $lints = (new TLint)->lint(
            new ConcatenationSpacing($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function handles_valid_multiline_concat()
    {
        $file = <<<file
<?php

echo '! ' . \$this->linter->getLintDescription() . PHP_EOL
                . \$this->node->getLine() . ' : `' . \$this->linter->getCodeLine(\$this->node->getLine()) . '`';

file;

        $lints = (new TLint)->lint(
            new ConcatenationSpacing($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function triggers_on_multiline_concat()
    {
        $file = <<<file
<?php

echo getcwd() . '/'
    .\$a;

file;

        $lints = (new TLint)->lint(
            new ConcatenationSpacing($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function triggers_on_multiline_concat_where_lines_do_not_start_with_concat()
    {
        $file = <<<file
<?php

       \$directory = 'images/articles' . DIRECTORY_SEPARATOR .
            date('Y') . DIRECTORY_SEPARATOR .
            date('m') . DIRECTORY_SEPARATOR .
            date('d') . DIRECTORY_SEPARATOR;

file;

        $lints = (new TLint)->lint(
            new ConcatenationSpacing($file)
        );

        $this->assertEquals(1, count($lints));
        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }
}
