<?php

namespace tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\Linters\PureRestControllers;
use Tighten\TLint;

class PureRestControllersTest extends TestCase
{
    /** @test */
    function catches_non_rest_public_methods_in_an_otherwise_restful_controller()
    {
        $file = <<<file
<?php

namespace App;

class Controller
{
    public function index()
    {
        return view('test.view', ['ok' => 'test']);
    }

    public function nonRest()
    {
        return 'nope';
    }
}
file;

        $lints = (new TLint)->lint(
            new PureRestControllers($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function does_not_trigger_on_non_restful_private_method()
    {
        $file = <<<file
<?php

namespace App;

class Controller
{
    public function index()
    {
        return view('test.view', ['ok' => 'test']);
    }

    private function nonRest()
    {
        return 'nope';
    }
}
file;

        $lints = (new TLint)->lint(
            new PureRestControllers($file)
        );

        $this->assertEmpty($lints);
    }
}
