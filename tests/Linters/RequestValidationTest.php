<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\RequestValidation;
use Tighten\TLint;

class RequestValidationTest extends TestCase
{
    /** @test */
    function catches_this_validate_method_usage()
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
            new RequestValidation($file)
        );

        $this->assertEquals(11, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function does_not_trigger_on_helper_function_usage()
    {
        $file = <<<file
<?php

class TestController extends Controller
{
    public function update()
    {
        request()->validate([
            'response' => 'required_without:file',
        ]);
    }
}
file;

        $lints = (new TLint)->lint(
            new RequestValidation($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_when_using_request_variable_method()
    {
        $file = <<<file
<?php

namespace App;

class ControllerA extends Controller
{
    public function store(ARequest \$request)
    {
        \$request()->validate(['name' => 'required']);
    }
}
file;

        $lints = (new TLint)->lint(
            new RequestValidation($file)
        );

        $this->assertEmpty($lints);
    }
}
