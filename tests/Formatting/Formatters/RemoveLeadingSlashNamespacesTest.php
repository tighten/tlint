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

use \Tighten\TLint;
use \PHPUnit\Framework\TestCase;

echo test;
file;

        $correctlyFormatted = <<<'file'
<?php

use Tighten\TLint;
use PHPUnit\Framework\TestCase;

echo test;
file;

        $formatted = (new TFormat)->format(
            new RemoveLeadingSlashNamespaces($file, '.php')
        );

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function catches_leading_slashes_in_static_calls()
    {
        $file = <<<file
<?php

echo \Auth::user()->name;
file;

        $correctlyFormatted = <<<'file'
<?php

echo Auth::user()->name;
file;

        $formatted = (new TFormat)->format(
            new RemoveLeadingSlashNamespaces($file, '.php')
        );

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function catches_leading_slashes_in_instantiations()
    {
        $file = <<<file
<?php

echo new \User();
file;

        $correctlyFormatted = <<<'file'
<?php

echo new User();
file;

        $formatted = (new TFormat)->format(
            new RemoveLeadingSlashNamespaces($file, '.php')
        );

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function does_not_throw_on_variable_class_static_calls()
    {
        $file = <<<'file'
<?php

namespace App\Newsboard\Factory;

class Relationships
{
    static function randomOrCreate($className)
    {
        if ($className::all()->count() > 0) {
            return $className::all()->random();
        }

        return factory($className)->create();
    }
}
file;

        $formatted = (new TFormat)->format(
            new RemoveLeadingSlashNamespaces($file, '.php')
        );

        $this->assertEquals($file, $formatted);
    }

    /** @test */
    public function does_not_throw_when_calling_class_in_a_namespaced_file()
    {
        $file = <<<'file'
<?php

namespace App\Nova;

class User extends BaseResource
{
    public static $model = \App\User::class;
}
file;

        $formatted = (new TFormat)->format(
            new RemoveLeadingSlashNamespaces($file, '.php')
        );

        $this->assertEquals($file, $formatted);
    }

    /** @test */
    public function catches_leading_slash_in_factories()
    {
        $file = <<<'file'
<?php

$factory->define(App\S::class, function (Faker\Generator $faker) {
    return [
        'user_id' => factory(App\User::class),
        'version_id' => factory(\App\J\V::class),
    ];
});
file;

$correctlyFormatted = <<<'file'
<?php

$factory->define(App\S::class, function (Faker\Generator $faker) {
    return [
        'user_id' => factory(App\User::class),
        'version_id' => factory(App\J\V::class),
    ];
});
file;

        $formatted = (new TFormat)->format(
            new RemoveLeadingSlashNamespaces($file, '.php')
        );

        $this->assertEquals($correctlyFormatted, $formatted);
    }
}
