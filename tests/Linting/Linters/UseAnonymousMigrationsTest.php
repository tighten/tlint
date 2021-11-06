<?php

namespace Tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\UseAnonymousMigrations;
use Tighten\TLint\TLint;

class UseAnonymousMigrationsTest extends TestCase
{
    /** @test */
    public function it_catches_named_class_migrations()
    {
        $file = <<<file
<?php

use Illuminate\Database\Migrations\Migration;

class CreateBuyRequestsTable extends Migration
{
    public function up()
    {
        //
    }

    public function down()
    {
        //
    }
}

file;

        $lints = (new TLint)->lint(
            new UseAnonymousMigrations($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function it_allows_anonymous_migrations()
    {
        $file = <<<file
<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        //
    }

    public function down()
    {
        //
    }
};

file;

        $lints = (new TLint)->lint(
            new UseAnonymousMigrations($file)
        );

        $this->assertEmpty($lints);
    }
}
