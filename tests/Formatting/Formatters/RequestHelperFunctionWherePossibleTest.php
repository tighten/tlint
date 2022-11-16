<?php

namespace Tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Formatters\RequestHelperFunctionWherePossible;
use Tighten\TLint\TFormat;

class RequestHelperFunctionWherePossibleTest extends TestCase
{
    /** @test */
    public function catches_get_method_usage()
    {
        $file = <<<'file'
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

        $expected = <<<'file'
<?php

namespace App;

class Controller
{
    public function index()
    {
        return SavedVehicle::findOrFail(request('savedVehicleId'));
    }
}
file;

        $formatted = (new TFormat)->format(new RequestHelperFunctionWherePossible($file));

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    public function ignores_other_request_methods()
    {
        $file = <<<'file'
<?php

namespace App;

class Controller
{
    public function index()
    {
        $validated = request()->validate(['test' => 'required']);

        if (request()->has('version') || request()->hasHeader('version')) {
            return request()->input('version') ?? request()->header('version');
        }
    }
}
file;

        $formatted = (new TFormat)->format(new RequestHelperFunctionWherePossible($file));

        $this->assertSame($file, $formatted);
    }
}
