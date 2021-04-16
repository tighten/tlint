<?php

namespace Tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\NoDump;
use Tighten\TLint\TLint;

class NoDumpTest extends TestCase
{
    static function codeToIgnore(): iterable
    {
        return [
            [
                <<<php
<?php

\$foo = "abc";
//dd(\$foo);

/**
* dd(\$foo);
*/

php,
            ], [
                <<<php
<?php

\$foo = "abc";
//dump(\$foo);

/**
* dump(\$foo);
*/

php,
            ], [
                <<<php
<?php

\$foo = "abc";
//var_dump(\$foo);

/**
 *
* var_dump(\$foo);
*/

php,
            ],
        ];
    }

    static function codeToTrigger(): iterable
    {
        return [
            [
                <<<php
<?php

\$foo = "abc";
dd(\$foo);

php,
            ], [
                <<<php
<?php

\$foo = "abc";
dump(\$foo);

php,
            ], [
                <<<php
<?php

\$foo = "abc";
var_dump(\$foo);

php,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider codeToTrigger
     */
    function does_trigger_on_call(string $file): void
    {
        $lints = (new TLint)->lint(
            new NoDump($file)
        );

        $this->assertEquals(4, $lints[0]->getNode()->getLine());
    }

    /**
     * @test
     * @dataProvider codeToIgnore
     */
    function does_not_trigger_on_call_in_comments(string $file): void
    {
        $lints = (new TLint)->lint(
            new NoDump($file)
        );

        $this->assertEmpty($lints);
    }
}
