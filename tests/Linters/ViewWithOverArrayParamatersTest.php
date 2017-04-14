<?php

use PHPUnit\Framework\TestCase;
use Tighten\Tighten;
use Tighten\Linters\ViewWithOverArrayParamaters;

class ViewWithOverArrayParamatersTest extends TestCase
{
    /** @test */
    public function catches_array_paramaters_with_view_in_controller_methods()
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
}
file;

        $lints = (new Tighten)->lint(
            new ViewWithOverArrayParamaters($file)
        );

        $this->assertEquals(9, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function catches_array_paramaters_with_view_in_routes()
    {
        $file = <<<file
<?php

\Route::get('test', function () {
    return view('test', ['test' => 'test']); 
});
file;

        $lints = (new Tighten)->lint(
            new ViewWithOverArrayParamaters($file)
        );

        $this->assertEquals(4, $lints[0]->getNode()->getLine());
    }
}
