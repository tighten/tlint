<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\SpacesAroundConcatenators;
use Tighten\TLint;

class SpacesAroundConcatenatorsTest extends TestCase
{
    /** @test */
    public function catches_concat_without_spaces()
    {
        $file = <<<file
<?php

\$baz = '';

echo "foo bar . ".\$baz;

file;

        $lints = (new TLint)->lint(
            new SpacesAroundConcatenators($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
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
            new SpacesAroundConcatenators($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
        $this->assertEquals(4, $lints[1]->getNode()->getLine());
    }

    /** @test */
    public function catches_multiple_concats_in_the_same_line()
    {
        $file = <<<file
<?php

echo "foo" . \$bar . "baz";
echo foo()."bar . ". \$baz ."bar".\$foo;

file;

        $lints = (new TLint)->lint(
            new SpacesAroundConcatenators($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
        $this->assertEquals(3, $lints[1]->getNode()->getLine());
        $this->assertEquals(4, $lints[2]->getNode()->getLine());
        $this->assertEquals(4, $lints[3]->getNode()->getLine());
    }

    /** @test */
    public function does_not_trigger_on_concat_with_spaces()
    {
        $file = <<<file
<?php

\$baz = '';

echo "foo bar . " . \$baz;

file;

        $lints = (new TLint)->lint(
            new SpacesAroundConcatenators($file)
        );

        $this->assertEmpty($lints);
    }
}
