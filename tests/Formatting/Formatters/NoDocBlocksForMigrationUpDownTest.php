<?php

namespace Tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Formatters\NoDocBlocksForMigrationUpDown;
use Tighten\TLint\TFormat;

class NoDocBlocksForMigrationUpDownTest extends TestCase
{
    /** @test */
    public function removes_up_and_down_docblocks()
    {
        $file = <<<file
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuyRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

file;

        $formatted = (new TFormat)->format(
            new NoDocBlocksForMigrationUpDown($file)
        );

        $correctlyFormatted = <<<file
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
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

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function doesnt_remove_other_migration_docblocks()
    {
        $file = <<<file
<?php

class CreateBuyRequestsTable extends Migration
{
    /**
     * Do extra stuff
     *
     * @return void
     */
    public function secretExtra()
    {
        //
    }
}

file;

        $formatted = (new TFormat)->format(
            new NoDocBlocksForMigrationUpDown($file)
        );

        $this->assertEquals($file, $formatted);
    }
}
