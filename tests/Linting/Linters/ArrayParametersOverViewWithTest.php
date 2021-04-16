<?php

namespace Tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\ArrayParametersOverViewWith;
use Tighten\TLint\TLint;

class ArrayParametersOverViewWithTest extends TestCase
{
    /** @test */
    function catches_view_with_method_usage_in_controller_methods()
    {
        $file = <<<file
<?php

namespace App;

class Controller
{
    function index()
    {
        return view('test.view')->with('ok', 'test');
    }
}
file;

        $lints = (new TLint)->lint(
            new ArrayParametersOverViewWith($file)
        );

        $this->assertEquals(9, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_view_with_method_usage_in_routes()
    {
        $file = <<<file
<?php

\Route::get('test', function () {
    return view('test')->with('test', 'test');
});
file;

        $lints = (new TLint)->lint(
            new ArrayParametersOverViewWith($file)
        );

        $this->assertEquals(4, $lints[0]->getNode()->getLine());
    }
}
