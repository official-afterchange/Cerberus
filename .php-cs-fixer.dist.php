<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/views', __DIR__ . '/config'])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
    // 🌟 Standards
    '@PSR12' => true,
    '@PSR12:risky' => true,

    // 🌟 Strict et moderne
    'declare_strict_types' => true,
    'void_return' => true,
    'strict_comparison' => true,
    'strict_param' => true,
    'self_accessor' => true,
    'final_class' => true,
    'final_public_method_for_abstract_class' => true,
    'modernize_types_casting' => true,
    'combine_consecutive_unsets' => true,
    'modernize_strpos' => true,

    // 🌟 Syntaxe
    'array_syntax' => ['syntax' => 'short'],
    'binary_operator_spaces' => ['default' => 'single_space'],
    'concat_space' => ['spacing' => 'one'],
    'single_quote' => true,
    'no_leading_import_slash' => true,
    'no_trailing_comma_in_singleline' => true,
    'single_class_element_per_statement' => true,
    'single_line_after_imports' => true,

    // 🌟 Nettoyage et organisation
    'no_unused_imports' => true,
    'ordered_imports' => ['sort_algorithm' => 'alpha'],
    'no_empty_phpdoc' => true,
    'no_blank_lines_after_class_opening' => true,
    'no_trailing_whitespace' => true,
    'no_trailing_whitespace_in_comment' => true,
    'single_blank_line_at_eof' => true,
    'blank_line_after_namespace' => true,
    'blank_line_after_opening_tag' => true,
    'trim_array_spaces' => true,
    'standardize_not_equals' => true,
    'simplified_null_return' => true,

    // 🌟 Modern PHP features
    'lambda_not_used_import' => true,
    'short_scalar_cast' => true,
    'native_function_invocation' => ['include' => ['@all']],
    'native_constant_invocation' => true,
    'no_unneeded_final_method' => true,
    'no_unneeded_control_parentheses' => true,
    'protected_to_private' => true,
    'nullable_type_declaration_for_default_null_value' => true,
])
    ->setFinder($finder)
    ->setUsingCache(true)
    ->setIndent("    ")
    ->setLineEnding("\n");