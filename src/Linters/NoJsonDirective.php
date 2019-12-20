<?php

namespace Tighten\Linters;

use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\CustomNode;
use Tighten\Linters\Concerns\LintsBladeTemplates;

class NoJsonDirective extends BaseLinter
{
    use LintsBladeTemplates;

    public const description = 'Use blade `{{ $model }}` auto escaping for models, and double quotes via json_encode over @json blade directive:'
        . ' `<vue-comp :values=\'@json($var)\'>` -> `<vue-comp :values="{{ $model }}">` OR `<vue-comp :values="{{ json_encode($var) }}">`';

    public function lint(Parser $parser)
    {
        $foundNodes = [];

        foreach ($this->getCodeLines() as $line => $codeLine) {
            $matches = [];

            // https://github.com/illuminate/view/blob/master/Compilers/BladeCompiler.php#L271
            preg_match(
                '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x',
                $codeLine,
                $matches
            );

            if (($matches[1] ?? null) === 'json') {
                $foundNodes[] = new CustomNode(['startLine' => $line + 1]);
            }
        }

        return $foundNodes;
    }
}
