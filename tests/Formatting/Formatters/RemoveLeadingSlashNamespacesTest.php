<?php

namespace Tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Formatters\RemoveLeadingSlashNamespaces;
use Tighten\TLint\TFormat;

class RemoveLeadingSlashNamespacesTest extends TestCase
{
    /** @test */
    public function catches_leading_slashes_in_use_statements()
    {
        $file = <<<file
<?php

namespace App;

use \Tighten\TLint;
use \PHPUnit\Framework\TestCase;

class TestClass
{
    public function test()
    {
        echo test;
    }
}
file;

        $correctlyFormatted = <<<'file'
<?php

namespace App;

use Tighten\TLint;
use PHPUnit\Framework\TestCase;

class TestClass
{
    public function test()
    {
        echo test;
    }
}
file;

        $formatted = (new TFormat())->format(
            new RemoveLeadingSlashNamespaces($file, '.php')
        );

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function does_not_catch_leading_slash_in_code()
    {
        $file = <<<'file'
<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $name = \Illuminate\Support\Facades\Auth::user()->name;

        $zip = new \ZipArchive;

        $class = new \stdClass;

        $model = \App\User::class;

        Validator::extend('recaptcha', 'App\Validators\ReCaptchaValidator@validate');
    }
}
file;

        $formatted = (new TFormat())->format(
            new RemoveLeadingSlashNamespaces($file, '.php')
        );

        $this->assertEquals($file, $formatted);
    }
}
