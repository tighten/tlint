<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\RestControllersMethodOrder;
use Tighten\TLint;

class RestControllersMethodOrderTest extends TestCase
{
    /** @test */
    public function catches_wrong_order_rest_methods()
    {
        $file = <<<file
<?php

namespace App;

class Controller
{
    use Thing;

    public function create()
    {
        return view('test.create', ['ok' => 'test']);
    }
    
    public function index()
    {
        return view('test.index', ['ok' => 'test']);
    }
}
file;

        $lints = (new TLint)->lint(
            new RestControllersMethodOrder($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
    }
}
