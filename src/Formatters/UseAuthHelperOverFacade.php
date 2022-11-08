<?php

namespace Tighten\TLint\Formatters;

use PhpParser\Lexer;
use PhpParser\Parser;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\Concerns\LintsBladeTemplates;
use Tighten\TLint\Linters\UseAuthHelperOverFacade as Linter;

class UseAuthHelperOverFacade extends BaseFormatter
{
    use LintsBladeTemplates;

    public const DESCRIPTION = 'Prefer the `auth()` helper function over the `Auth` Facade.';

    public function format(Parser $parser, Lexer $lexer)
    {
        foreach ($this->getCodeLines() as $codeLine) {
            $matches = [];

            preg_match_all(
                Linter::AUTH_SEARCH,
                $codeLine,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                if (in_array($match[1], Linter::AUTH_HELPER_METHODS)) {
                    $this->code = str_replace($match[0], 'auth()->' . $match[1] . '(', $this->code);
                }
            }
        }

        return $this->code;
    }
}
