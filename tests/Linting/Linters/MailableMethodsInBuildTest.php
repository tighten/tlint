<?php

namespace testing\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\Linters\MailableMethodsInBuild;
use Tighten\TLint;

class MailableMethodsInBuildTest extends TestCase
{
    /** @test */
    function catches_mailable_methods_in_constructor()
    {
        $file = <<<file
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendGarageLink extends Mailable
{
    use Queueable, SerializesModels;

    public \$url;

    public function __construct(\$url)
    {
        \$this->url = \$url;
        \$this->from('noreply@delivermyride.com', config('name'));
        \$this->subject(config('name') . ' Garage');
    }

    public function build()
    {
        return \$this->view('auth.emails.email-login');
    }
}

file;

        $lints = (new TLint)->lint(
            new MailableMethodsInBuild($file)
        );

        $this->assertEquals(15, $lints[0]->getNode()->getLine());
    }

    /** @test */
    function does_not_trigger_on_methods_in_build()
    {
        $file = <<<file
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendGarageLink extends Mailable
{
    use Queueable, SerializesModels;

    public \$url;

    public function __construct(\$url)
    {
        \$this->url = \$url;
    }

    public function build()
    {
        \$this->from('noreply@delivermyride.com', config('name'));
        \$this->subject(config('name') . ' Garage');
        
        return \$this->view('auth.emails.email-login');
    }
}

file;

        $lints = (new TLint)->lint(
            new MailableMethodsInBuild($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    function does_not_trigger_on_non_mailable()
    {
        $file = <<<file
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendGarageLink
{
    use Queueable, SerializesModels;

    public \$url;

    public function __construct(\$url)
    {
        \$this->url = \$url;
        \$this->subject(config('name') . ' Garage');
    }

    public function build()
    {
        \$this->from('noreply@delivermyride.com', config('name'));
        
        return \$this->view('auth.emails.email-login');
    }
}

file;

        $lints = (new TLint)->lint(
            new MailableMethodsInBuild($file)
        );

        $this->assertEmpty($lints);
    }
}
