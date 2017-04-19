<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\RequestHelperFunctionWherePossible;
use Tighten\TLint;

class RequestHelperFunctionWherePossibleTest extends TestCase
{
    /** @test */
    public function catches_get_method_usage()
    {
        $file = <<<file
<?php

namespace App;

class Controller
{
    public function index()
    {
        return SavedVehicle::findOrFail(request()->get('savedVehicleId'));
    }
}
file;

        $lints = (new TLint)->lint(
            new RequestHelperFunctionWherePossible($file)
        );

        $this->assertEquals(9, $lints[0]->getNode()->getLine());
    }
}
