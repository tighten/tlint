<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\RequestHelperValidation;
use Tighten\TLint;

class RequestHelperValidationTest extends TestCase
{
    /** @test */
    public function catches_validate_method_usage_in_controller()
    {
        $file = <<<file
<?php

namespace App;

use App\Http\Controllers\Controller;

class ControllerA extends Controller
{
    public function store()
    {
        \$this->validate(['name' => 'required']);
    }
}
file;

        $lints = (new TLint)->lint(
            new RequestHelperValidation($file)
        );

        $this->assertEquals(11, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function does_not_trigger_on_non_controller()
    {
        $file = <<<file
<?php

namespace App;

class ControllerA
{
    public function store()
    {
        \$this->validate(['name' => 'required']);
    }
}
file;

        $lints = (new TLint)->lint(
            new RequestHelperValidation($file)
        );

        $this->assertEmpty($lints);
    }
}
