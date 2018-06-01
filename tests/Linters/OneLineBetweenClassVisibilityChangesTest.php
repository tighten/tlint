<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\OneLineBetweenClassVisibilityChanges;
use Tighten\TLint;

class OneLineBetweenClassVisibilityChangesTest extends TestCase
{
    /** @test */
    public function catches_missing_line_between_visibility_changes()
    {
        $file = <<<file
<?php

namespace App;

class Thing
{
    protected const OK = 1;
    private \$ok;
}
file;

        $lints = (new TLint)->lint(
            new OneLineBetweenClassVisibilityChanges($file)
        );

        $this->assertEquals(8, $lints[0]->getNode()->getLine());
    }
}
