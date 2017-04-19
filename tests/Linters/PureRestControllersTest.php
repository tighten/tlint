<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\PureRestControllers;
use Tighten\TLint;

class PureRestControllersTest extends TestCase
{
    /** @test */
    public function catches_non_rest_methods_in_an_otherwise_restful_controller()
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
}
