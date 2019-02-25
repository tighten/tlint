<?php

use PHPUnit\Framework\TestCase;
use Tighten\TLint;
use Tighten\Linters\ViewWithOverArrayParameters;

class ViewWithOverArrayParametersTest extends TestCase
{
    /** @test */
    function catches_array_parameters_with_view_in_controller_methods()
    {
        $file = <<<file
<?php

namespace App;

class Controller
{
    function index()
    {
        return view('test.view', ['ok' => 'test']);
    }
}
file;

        $lints = (new TLint)->lint(
            new ViewWithOverArrayParameters($file)
        );

        $this->assertEquals(9, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_array_parameters_with_view_in_routes()
    {
        $file = <<<file
<?php

\Route::get('test', function () {
    return view('test', ['test' => 'test']); 
});
file;

        $lints = (new TLint)->lint(
            new ViewWithOverArrayParameters($file)
        );

        $this->assertEquals(4, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function does_not_trigger_on_variable_function_call()
    {
        $file = <<<file
<?php

\$thing();
file;

        $lints = (new TLint)->lint(
            new ViewWithOverArrayParameters($file)
        );

        $this->assertEmpty($lints);
    }
}
