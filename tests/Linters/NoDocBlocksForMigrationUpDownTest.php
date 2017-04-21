<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\QualifiedNamesOnlyForClassName;
use Tighten\Linters\NoDocBlocksForMigrationUpDown;
use Tighten\TLint;

class NoDocBlocksForMigrationUpDownTest extends TestCase
{
    /** @test */
    public function catches_doc_blocks_on_up_and_down()
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

        $lints = (new TLint)->lint(
            new NoDocBlocksForMigrationUpDown($file)
        );

        $this->assertEquals(14, $lints[0]->getNode()->getLine());
        $this->assertEquals(24, $lints[1]->getNode()->getLine());
    }
}
