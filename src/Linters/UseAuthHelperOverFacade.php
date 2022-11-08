<?php

namespace Tighten\TLint\Linters;

use PhpParser\Parser;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\CustomNode;
use Tighten\TLint\Linters\Concerns\LintsBladeTemplates;

class UseAuthHelperOverFacade extends BaseLinter
{
    use LintsBladeTemplates;

    public const DESCRIPTION = 'Prefer the `auth()` helper function over the `Auth` Facade.';

    public const AUTH_SEARCH = '/(?:\\\Illuminate\\\Support\\\Facades\\\)?Auth::([a-zA-Z]+)\(/';

    public const AUTH_HELPER_METHODS = [
        //'routes', // allow routes() to be used
        'extend',
        'provider',
        'loginUsingId',
        'user',
        'guard',
        'createUserProvider',
        'onceBasic',
        'attempt',
        'hasUser',
        'check',
        'guest',
        'once',
        'onceUsingId',
        'validate',
        'viaRemember',
        'logoutOtherDevices',
        'id',
        'login',
        'logout',
        'logoutCurrentDevice',
        'setUser',
        'shouldUse',
    ];

    public function lint(Parser $parser)
    {
        $foundNodes = [];

        foreach ($this->getCodeLines() as $line => $codeLine) {
            $matches = [];

            preg_match_all(
                self::AUTH_SEARCH,
                $codeLine,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                if (in_array($match[1], self::AUTH_HELPER_METHODS)) {
                    $foundNodes[] = new CustomNode(['startLine' => $line + 1]);
                }
            }
        }

        return $foundNodes;
    }
}
