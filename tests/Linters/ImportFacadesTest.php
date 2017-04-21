<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\ImportFacades;
use Tighten\TLint;

class ImportFacadesTest extends TestCase
{
    /** @test */
    public function catches_alias_usage_without_import()
    {
        $file = <<<file
<?php

var_dump(Hash::make('test'));
file;

        $lints = (new TLint)->lint(
            new ImportFacades($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function does_not_trigger_on_facade_usage_with_import()
    {
        $file = <<<file
<?php

use Illuminate\Support\Facades\Hash;

var_dump(Hash::make('test'));
file;

        $lints = (new TLint)->lint(
            new ImportFacades($file)
        );

        $this->assertEmpty($lints);
    }
}
