<?php

namespace Tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\NoDatesPropertyOnModels;
use Tighten\TLint\TLint;

class NoDatesPropertyOnModelsTest extends TestCase
{
    /** @test */
    public function lints_dates_property_on_model()
    {
        $file = <<<'file'
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $dates = ['published_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
file;

        $lints = (new TLint())->lint(new NoDatesPropertyOnModels($file));

        $this->assertEquals(9, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function lints_dates_property_on_pivot_model()
    {
        $file = <<<'file'
<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AuthorPost extends Pivot
{
    public $incrementing = false;

    protected $dates = ['reviewed_at'];
}
file;

        $lints = (new TLint())->lint(new NoDatesPropertyOnModels($file));

        $this->assertEquals(11, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function lints_dates_property_on_authenticatable()
    {
        $file = <<<'file'
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email'];
    protected $dates = ['reviewed_at'];
}
file;

        $lints = (new TLint())->lint(new NoDatesPropertyOnModels($file));

        $this->assertEquals(13, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function ignores_dates_property_on_non_model()
    {
        $file = <<<'file'
<?php

namespace App\Events;

class Birthday
{
    protected $dates = ['date'];
}
file;

        $this->assertEmpty((new TLint())->lint(new NoDatesPropertyOnModels($file)));
    }
}
