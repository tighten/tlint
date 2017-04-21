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
}
