<?php

namespace tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\RequestHelperFunctionWherePossible;
use Tighten\TLint\TLint;

class RequestHelperFunctionWherePossibleTest extends TestCase
{
    /** @test */
    function catches_get_method_usage()
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

    /** @test */
    function does_not_trigger_on_new_instance_method_calls()
    {
        $file = <<<file
<?php

(new TableBuilder(new TablePresenter()))
        ->column();
file;

        $lints = (new TLint)->lint(
            new RequestHelperFunctionWherePossible($file)
        );

        $this->assertEmpty($lints);
    }
}
