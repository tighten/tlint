<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\FQCNOnlyForClassName;
use Tighten\Linters\UseConfigOverEnv;
use Tighten\TLint;

class UseConfigOverEnvTest extends TestCase
{
    /** @test */
    public function catches_direct_usage_of_env_function()
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
