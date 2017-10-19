<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\RequestHelperFunctionWherePossible;
use Tighten\TLint;

class RequestHelperFunctionWherePossibleTest extends TestCase
{
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

    public function does_not_trigger_on_new_instance_method_calls()
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
