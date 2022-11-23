<?php

namespace Tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Formatters\UseAnonymousMigrations;
use Tighten\TLint\TFormat;

class UseAnonymousMigrationsTest extends TestCase
{
    /** @test */
    public function it_converts_named_migration_to_anonymous_migration()
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

        $formatted = (new TFormat())->format(
            new UseAnonymousMigrations($file)
        );

        $correctlyFormatted = <<<file
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

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function it_ignores_anonymous_migrations()
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

        $formatted = (new TFormat())->format(
            new UseAnonymousMigrations($file)
        );

        $this->assertEquals($file, $formatted);
    }
}
