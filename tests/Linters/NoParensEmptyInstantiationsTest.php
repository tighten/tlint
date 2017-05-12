<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\NoParensEmptyInstantiations;
use Tighten\TLint;

class NoParensEmptyInstantiationsTest extends TestCase
{
    /** @test */
    public function catches_unnecessary_parens()
    {
        $file = <<<file
<?php

\$ok = new Thing();
file;

        $lints = (new TLint)->lint(
            new NoParensEmptyInstantiations($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function ignores_necessary_parens()
    {
        $file = <<<file
<?php

\$ok = new Thing(1, 2);
file;

        $lints = (new TLint)->lint(
            new NoParensEmptyInstantiations($file)
        );

        $this->assertEmpty($lints);
    }
}
