<?php

namespace testing\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\Linters\ModelMethodOrder;
use Tighten\TLint;

class ModelMethodOrderTest extends TestCase
{
    /** @test */
    function catches_wrong_order_for_model_methods()
    {
        $file = file_get_contents(__DIR__.'/../../fixtures/Models/Invalid/Thing.php');

        $lints = (new TLint)->lint(
            new ModelMethodOrder($file)
        );

        $this->assertInstanceOf(ModelMethodOrder::class, $lints[0]->getLinter());

        $this->assertEquals(implode(PHP_EOL, [
            'Model method order should be: booting > boot > booted > custom_static > relationships > scopes > accessors > mutators > custom',
            'Methods are expected to be ordered like:',
            ' * booting() is matched as "booting"',
            ' * boot() is matched as "boot"',
            ' * booted() is matched as "booted"',
            ' * make() is matched as "custom_static"',
            ' * makeInternal() is matched as "custom_static"',
            ' * category() is matched as "relationship"',
            ' * comments() is matched as "relationship"',
            ' * images() is matched as "relationship"',
            ' * media() is matched as "relationship"',
            ' * phone() is matched as "relationship"',
            ' * tags() is matched as "relationship"',
            ' * scopeWhereActive() is matched as "scope"',
            ' * scopeWhereInactive() is matched as "scope"',
            ' * getFirstNameAttribute() is matched as "accessor"',
            ' * setFirstNameAttribute() is matched as "mutator"',
            ' * setLastNameAttribute() is matched as "mutator"',
            ' * activate() is matched as "custom"',
            ' * publish() is matched as "custom"',
        ]), $lints[0]->getLinter()->getLintDescription());

        $this->assertEquals(11, $lints[0]->getNode()->getLine());
    }
}
