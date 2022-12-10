<?php

namespace Tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\RemoveLeadingSlashNamespaces;
use Tighten\TLint\TLint;

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

        $lints = (new TLint())->lint(
            new RemoveLeadingSlashNamespaces($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
        $this->assertEquals(4, $lints[1]->getNode()->getLine());
    }

    /** @test */
    public function catches_leading_slashes_in_static_calls()
    {
        $file = <<<file
<?php

echo \Auth::user()->name;
file;

        $lints = (new TLint())->lint(
            new RemoveLeadingSlashNamespaces($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function catches_leading_slashes_in_instantiations()
    {
        $file = <<<file
<?php

echo new \User();
file;

        $lints = (new TLint())->lint(
            new RemoveLeadingSlashNamespaces($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function does_not_throw_on_variable_class_static_calls()
    {
        $file = <<<file
<?php

namespace App\Newsboard\Factory;

class Relationships
{
    static function randomOrCreate(\$className)
    {
        if (\$className::all()->count() > 0) {
            return \$className::all()->random();
        }

        return factory(\$className)->create();
    }
}
file;

        $lints = (new TLint())->lint(
            new RemoveLeadingSlashNamespaces($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function does_not_throw_when_calling_class_in_a_namespaced_file()
    {
        $file = <<<file
<?php

namespace App\Nova;

class User extends BaseResource
{
    public static \$model = \App\User::class;
}
file;

        $lints = (new TLint())->lint(
            new RemoveLeadingSlashNamespaces($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function catches_leading_slash_in_factories()
    {
        $file = <<<file
<?php

\$factory->define(App\S::class, function (Faker\Generator \$faker) {
    return [
        'user_id' => factory(App\User::class),
        'version_id' => factory(\App\J\V::class),
    ];
});
file;

        $lints = (new TLint())->lint(
            new RemoveLeadingSlashNamespaces($file)
        );

        $this->assertEquals(6, $lints[0]->getNode()->getLine());
    }
}
