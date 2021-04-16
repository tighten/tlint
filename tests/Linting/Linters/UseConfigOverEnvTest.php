<?php

namespace tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\UseConfigOverEnv;
use Tighten\TLint\TLint;

class UseConfigOverEnvTest extends TestCase
{
    /** @test */
    function catches_direct_usage_of_env_function()
    {
        $file = <<<file
<?php

echo env('thing');
file;

        $lints = (new TLint)->lint(
            new UseConfigOverEnv($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }
}
