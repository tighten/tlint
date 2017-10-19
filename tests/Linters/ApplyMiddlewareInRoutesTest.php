<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\ApplyMiddlewareInRoutes;
use Tighten\TLint;

class ApplyMiddlewareInRoutesTest extends TestCase
{
    /** @test */
    public function catches_middleware_in_controllers()
    {
        $file = <<<file
<?php

namespace App\Http\Controllers;

class BuyRequestController extends Controller
{
    public function __construct()
    {
        \$this->middleware('auth');
    }
}

file;

        $lints = (new TLint)->lint(
            new ApplyMiddlewareInRoutes($file)
        );

        $this->assertEquals(9, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function does_not_throw_on_methods_calls_on_instantiations()
    {
        $file = <<<file
<?php

namespace App\Http\Controllers;

class BuyRequestController extends Controller
{
    public function __construct()
    {
        return (new TableBuilder(new EmptyTablePresenter()))->column();
    }
}

file;

        $lints = (new TLint)->lint(
            new ApplyMiddlewareInRoutes($file)
        );

        $this->assertEmpty($lints);
    }
}
