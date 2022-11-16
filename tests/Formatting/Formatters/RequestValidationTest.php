<?php

namespace Tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Formatters\RequestValidation;
use Tighten\TLint\TFormat;

class RequestValidationTest extends TestCase
{
    /** @test */
    public function catches_this_validate_method_usage()
    {
        $file = <<<'file'
<?php

namespace App;

use App\Http\Controllers\Controller;

class ControllerA extends Controller
{
    public function store()
    {
        $this->validate(['name' => 'required'], ['name.required' => 'Name is required']);
    }
}
file;

        $expected = <<<'file'
<?php

namespace App;

use App\Http\Controllers\Controller;

class ControllerA extends Controller
{
    public function store()
    {
        request()->validate(['name' => 'required'], ['name.required' => 'Name is required']);
    }
}
file;

        $formatted = (new TFormat)->format(new RequestValidation($file));

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    public function does_not_trigger_on_helper_function_usage()
    {
        $file = <<<'file'
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

        $formatted = (new TFormat)->format(new RequestValidation($file));

        $this->assertSame($file, $formatted);
    }

    /** @test */
    public function does_not_trigger_when_using_request_variable_method()
    {
        $file = <<<'file'
<?php

namespace App;

class ControllerA extends Controller
{
    public function store(ARequest $request)
    {
        $request->validate([]);
    }
}
file;

        $formatted = (new TFormat)->format(new RequestValidation($file));

        $this->assertSame($file, $formatted);
    }

    /** @test */
    public function does_not_cause_php_notice_when_value_is_not_an_expression_with_a_name()
    {
        $file = <<<'file'
<?php

namespace App;

class ControllerA extends Controller
{
    public function store()
    {
        return back()
            ->with('errors', (new ViewErrorBag)->add('field', 'message'));
    }
}
file;

        $formatted = (new TFormat)->format(new RequestValidation($file));

        $this->assertSame($file, $formatted);
    }
}
