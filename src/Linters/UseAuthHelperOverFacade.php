<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Illuminate\BladeCompiler;

class UseAuthHelperOverFacade extends BaseLinter
{
    public const DESCRIPTION = 'Prefer the `auth()` helper function over the `Auth` Facade.';

    public function __construct($code, $filename = null)
    {
        if (preg_match('/\.blade\.php$/i', $filename)) {
            $bladeCompiler = new BladeCompiler(null, sys_get_temp_dir());
            $code = $bladeCompiler->compileString($code);
        }

        parent::__construct($code, $filename);
    }

    protected function visitor(): Closure
    {
        return function (Node $node) {
            static $usesAuthFacade = false;

            if ($node instanceof Node\Stmt\UseUse && $node->name instanceof Node\Name) {
                if ($node->name->toString() === 'Illuminate\Support\Facades\Auth') {
                    $usesAuthFacade = true;
                }
            }

            return $node instanceof Node\Expr\StaticCall
                // use Illuminate\Support\Facades\Auth and calling Auth::
                && (($usesAuthFacade && $node->class instanceof Node\Name && $node->class->toString() === 'Auth')
                    // FQCN usage
                    || (
                        $node->class instanceof Node\Name
                        && $node->class->toString() === 'Illuminate\Support\Facades\Auth'
                    ))
                && ($node->class instanceof Node\Name && $node->name->name !== 'routes');
        };
    }
}
