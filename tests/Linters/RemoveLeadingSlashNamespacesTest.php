<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\RemoveLeadingSlashNamespaces;
use Tighten\TLint;

class RemoveLeadingSlashNamespacesTest extends TestCase
{
    /** @test */
    public function catches_leading_slashes_in_use_statements()
    {
        $file = <<<file
<?php

use \Tighten\TLint;
use \PHPUnit\Framework\TestCase;

echo test;
file;

        $lints = (new TLint)->lint(
            new RemoveLeadingSlashNamespaces($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
        $this->assertEquals(4, $lints[1]->getNode()->getLine());
    }

    /** @test */
    public function catches_leading_slashes_in_static_calls()
    {
        $file = <<<file
<?php

echo \Auth::user()->name;
file;

        $lints = (new TLint)->lint(
            new RemoveLeadingSlashNamespaces($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function catches_leading_slashes_in_instantiations()
    {
        $file = <<<file
<?php

echo new \User();
file;

        $lints = (new TLint)->lint(
            new RemoveLeadingSlashNamespaces($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }
}
