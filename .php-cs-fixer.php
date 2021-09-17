<?php

use PhpCsFixer\Config;
use Symfony\Component\Finder\Finder;

$finder = Finder::create()->in(__DIR__)->name('*.php')
    ->exclude(['tests/fixtures', 'vendor'])
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config)->setFinder($finder)->setRules([
    '@PSR12' => true,
    'array_indentation' => true,
    'binary_operator_spaces' => [
        'operators' => ['|' => 'no_space'],
    ],
    'blank_line_before_statement' => [
        'statements' => ['return', 'throw', 'try'],
    ],
    'concat_space' => [
        'spacing' => 'one',
    ],
    'function_typehint_space' => true,
    'native_function_casing' => true,
    'new_with_braces' => false, // Override PSR12
    'no_extra_blank_lines' => [
        'tokens' => ['break', 'case', 'continue', 'curly_brace_block', 'default', 'extra', 'parenthesis_brace_block', 'return', 'square_brace_block', 'switch', 'throw', 'use', 'use_trait'],
    ],
    'no_leading_namespace_whitespace' => true,
    'no_spaces_around_offset' => true,
    'no_unused_imports' => true,
    'no_useless_else' => true,
    'not_operator_with_successor_space' => true,
    'object_operator_without_whitespace' => true,
    'ordered_imports' => [
        'sort_algorithm' => 'alpha', // Override PSR12
    ],
    'single_quote' => true,
    'trailing_comma_in_multiline' => [
        'after_heredoc' => true,
    ],
    'whitespace_after_comma_in_array' => true,
]);
