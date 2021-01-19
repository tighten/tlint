<?php

namespace testing\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\Linters\NoRequestAll;
use Tighten\TLint;

class NoRequestAllTest extends TestCase
{
    /** @test */
    function catches_request_all_with_variable()
    {
        $file = <<<'file'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController
{
    public function store(Request $request)
    {
        $user = User::create($request->all());

        return response()->json($user);
    }
}
file;

        $lints = (new TLint)->lint(new NoRequestAll($file));

        $this->assertEquals(11, $lints[0]->getNode()->getLine());
    }

        /** @test */
    function catches_request_all_with_helper()
    {
        $file = <<<'file'
<?php

namespace App\Http\Controllers;

class UserController
{
    public function store()
    {
        $user = User::create(request()->all());

        return response()->json($user);
    }
}
file;

        $lints = (new TLint)->lint(new NoRequestAll($file));

        $this->assertEquals(9, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function catches_request_all_with_facade()
    {
        $file = <<<'file'
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;

class UserController
{
    public function store()
    {
        $user = User::create(Request::all());

        return response()->json($user);
    }
}
file;

        $lints = (new TLint)->lint(new NoRequestAll($file));

        $this->assertEquals(11, $lints[0]->getNode()->getLine());
    }
}
