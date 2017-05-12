<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class ImportFacades extends BaseLinter
{
    private static $aliases = [
        'App' => 'Illuminate\Support\Facades\App',
        'Artisan' => 'Illuminate\Support\Facades\Artisan',
        'Auth' => 'Illuminate\Support\Facades\Auth',
        'Blade' => 'Illuminate\Support\Facades\Blade',
        'Broadcast' => 'Illuminate\Support\Facades\Broadcast',
        'Bus' => 'Illuminate\Support\Facades\Bus',
        'Cache' => 'Illuminate\Support\Facades\Cache',
        'Config' => 'Illuminate\Support\Facades\Config',
        'Cookie' => 'Illuminate\Support\Facades\Cookie',
        'Crypt' => 'Illuminate\Support\Facades\Crypt',
        'DB' => 'Illuminate\Support\Facades\DB',
        'Eloquent' => 'Illuminate\Database\Eloquent\Model',
        'Event' => 'Illuminate\Support\Facades\Event',
        'File' => 'Illuminate\Support\Facades\File',
        'Gate' => 'Illuminate\Support\Facades\Gate',
        'Hash' => 'Illuminate\Support\Facades\Hash',
        'Lang' => 'Illuminate\Support\Facades\Lang',
        'Log' => 'Illuminate\Support\Facades\Log',
        'Mail' => 'Illuminate\Support\Facades\Mail',
        'Notification' => 'Illuminate\Support\Facades\Notification',
        'Password' => 'Illuminate\Support\Facades\Password',
        'Queue' => 'Illuminate\Support\Facades\Queue',
        'Redirect' => 'Illuminate\Support\Facades\Redirect',
        'Redis' => 'Illuminate\Support\Facades\Redis',
        'Request' => 'Illuminate\Support\Facades\Request',
        'Response' => 'Illuminate\Support\Facades\Response',
        'Route' => 'Illuminate\Support\Facades\Route',
        'Schema' => 'Illuminate\Support\Facades\Schema',
        'Session' => 'Illuminate\Support\Facades\Session',
        'Storage' => 'Illuminate\Support\Facades\Storage',
        'URL' => 'Illuminate\Support\Facades\URL',
        'Validator' => 'Illuminate\Support\Facades\Validator',
        'View' => 'Illuminate\Support\Facades\View',
    ];
    protected $description = "Import facades (don't use aliases).";

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            static $useNames = [];

            if ($node instanceof Node\Stmt\UseUse) {
                $useNames[] = $node->name->toString();
            }

            return $node instanceof Node\Expr\StaticCall
                && (
                    $node->class instanceof Node\Name
                    && in_array($node->class->toString(), array_keys(static::$aliases))
                    && !in_array(static::$aliases[$node->class->toString()], $useNames)
                );
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
