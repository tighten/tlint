<?php

namespace Tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Formatters\ArrayParametersOverViewWith;
use Tighten\TLint\TFormat;

class ArrayParametersOverViewWithTest extends TestCase
{
    /** @test */
    public function catches_view_with_method_usage_in_controller_methods()
    {
        $file = <<<'file'
<?php

namespace App;

class Controller
{
    function index()
    {
        return view('test.view')->with('testing', '1212');
    }
}
file;

        $expected = <<<'file'
<?php

namespace App;

class Controller
{
    function index()
    {
        return view('test.view', ['testing' => '1212']);
    }
}
file;

        $formatted = (new TFormat)->format(new ArrayParametersOverViewWith($file));

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    public function catches_view_with_chained_method_usage_in_controller_methods()
    {
        $file = <<<'file'
<?php

namespace App;

class Controller
{
    function index()
    {
        return view('test.view')->with('first', 'yes')->with('second', 'no')->with('third', 'maybe');
    }
}
file;

        $expected = <<<'file'
<?php

namespace App;

class Controller
{
    function index()
    {
        return view('test.view', ['first' => 'yes', 'second' => 'no', 'third' => 'maybe']);
    }
}
file;

        $formatted = (new TFormat)->format(new ArrayParametersOverViewWith($file));

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    public function catches_view_with_chained_new_lines_method_usage_in_controller_methods()
    {
        $file = <<<'file'
<?php

namespace App;

class Controller
{
    function index()
    {
        return view('test.view')
            ->with('first', 'gold')
            ->with('second', 'silver')
            ->with('third', 'bronze');
    }
}
file;

        $expected = <<<'file'
<?php

namespace App;

class Controller
{
    function index()
    {
        return view('test.view', ['first' => 'gold', 'second' => 'silver', 'third' => 'bronze']);
    }
}
file;

        $formatted = (new TFormat)->format(new ArrayParametersOverViewWith($file));

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    public function catches_view_with_method_usage_in_routes()
    {
        $file = <<<file
<?php

\Route::get('test', function () {
    return view('test')->with('test', 'test');
});
file;

        $expected = <<<file
<?php

\Route::get('test', function () {
    return view('test', ['test' => 'test']);
});
file;

        $formatted = (new TFormat)->format(new ArrayParametersOverViewWith($file));

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    public function catches_multiple_view_with_usage_in_controller_methods()
    {
        $file = <<<'file'
<?php

namespace App;

class Controller
{
    function index()
    {
        return view('test.index')->with('first', 'yes');
    }

    function show()
    {
        return view('test.show', ['first' => 'yes']);
    }

    function create()
    {
        return view('test.create');
    }

    function edit()
    {
        return view('test.edit')->with('first', 'yes')->with('second', 'no');
    }
}
file;

        $expected = <<<'file'
<?php

namespace App;

class Controller
{
    function index()
    {
        return view('test.index', ['first' => 'yes']);
    }

    function show()
    {
        return view('test.show', ['first' => 'yes']);
    }

    function create()
    {
        return view('test.create');
    }

    function edit()
    {
        return view('test.edit', ['first' => 'yes', 'second' => 'no']);
    }
}
file;

        $formatted = (new TFormat)->format(new ArrayParametersOverViewWith($file));

        $this->assertSame($expected, $formatted);
    }
}
