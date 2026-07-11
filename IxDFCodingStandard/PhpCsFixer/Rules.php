<?php declare(strict_types=1);

namespace IxDFCodingStandard\PhpCsFixer;

/**
 * Shared PHP-CS-Fixer rules for IxDF projects, based on PER Coding Style.
 * @see https://localheinz.com/articles/2023/03/10/sharing-configurations-for-php-cs-fixer-across-projects/
 * @see https://mlocati.github.io/php-cs-fixer-configurator/
 * @api
 */
final class Rules
{
    // phpcs:disable SlevomatCodingStandard.Functions.FunctionLength.FunctionLength -- a flat rule list, not logic
    // phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
    /**
     * @see https://mlocati.github.io/php-cs-fixer-configurator/
     * @return array<string, array<string, mixed>|bool>
     */
    public static function get(): array
    {
        // phpcs:enable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
        return [
            // Basic PER Coding Style 3.0 ruleset plus our overrides for it, see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/ruleSets/PER-CS3x0.rst
            '@PER-CS3x0' => true, // https://www.php-fig.org/per/coding-style/
            // Auto-modernize syntax to the target PHP/PHPUnit version. Renovate bumps PHP-CS-Fixer, so new migration rules arrive for free.
            '@PHP8x4Migration' => true,
            '@PHP8x4Migration:risky' => true,
            '@PHPUnit10x0Migration:risky' => true,
            'use_arrow_functions' => false, // long closure syntax is sometimes more readable; do not force fn() (pulled in by @PHP8x4Migration:risky)
            'new_with_parentheses' => ['anonymous_class' => true], // It will be changed in PHP-CS-Fixer v4.0 (but we want to enforce it), see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/pull/8148
            // overrides for PER-CS2.0/PER-CS3.0
            'concat_space' => ['spacing' => 'none'], // make strings shorter "'hello' . $name . '!'" => "'hello'.$name.'!'"
            'blank_line_after_opening_tag' => false, // it makes "<?php declare(strict_types=1);" multiline (and more verbose)

            // Additional rules on the top of PER-CS2
            // Please keep these rules alphabetically
            'align_multiline_comment' => ['comment_type' => 'phpdocs_only'],
            'array_indentation' => true,
            'assign_null_coalescing_to_coalesce_equal' => true,
            'binary_operator_spaces' => ['default' => 'single_space'],
            'cast_spaces' => ['space' => 'single'],
            'class_attributes_separation' => ['elements' => ['method' => 'one']],
            'declare_strict_types' => true, // risky
            'explicit_string_variable' => true,
            // 'final_class' is deliberately NOT enabled: it cannot see subclasses in other files and finalizes extended classes.
            // The report-only SlevomatCodingStandard.Classes.RequireAbstractOrFinal sniff enforces the same policy safely.
            'final_public_method_for_abstract_class' => true, // risky
            'general_phpdoc_annotation_remove' => [
                'annotations' => [
                    // '@api' is intentionally NOT removed: it marks the supported public/BC surface.
                    'access',
                    'author',
                    'category',
                    'copyright',
                    'created',
                    'license',
                    'link',
                    'package',
                    'since',
                    'subpackage',
                    'version',
                ],
            ],
            'general_phpdoc_tag_rename' => [
                'replacements' => [
                    'inheritdoc' => 'inheritDoc',
                ],
            ],
            'global_namespace_import' => [
                'import_classes' => false,
                'import_constants' => false,
            ],
            'heredoc_to_nowdoc' => true,
            'is_null' => true, // risky
            'logical_operators' => true, // risky
            'modernize_types_casting' => true, // risky
            'mb_str_functions' => true, // risky
            'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
            'native_constant_invocation' => ['strict' => false], // risky; non-strict: keep `\` on userland global constants (e.g. PHPCS token constants)
            'native_function_casing' => true,
            'no_binary_string' => true,
            'no_empty_comment' => true,
            'no_empty_phpdoc' => true,
            'no_empty_statement' => true,
            'no_extra_blank_lines' => ['tokens' => ['extra', 'curly_brace_block']],
            'no_homoglyph_names' => true, // risky
            'no_leading_namespace_whitespace' => true,
            'no_mixed_echo_print' => true,
            'no_short_bool_cast' => true,
            'no_singleline_whitespace_before_semicolons' => true,
            'no_spaces_around_offset' => true,
            // allow_mixed: keep `@return mixed` on magic methods (__call etc.), PHPCS requires at least a @return annotation there
            'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
            'no_trailing_comma_in_singleline' => [
                'elements' => ['arguments', 'array', 'group_import'], // excludes 'array_destructuring' enabled by default
            ],
            'no_unneeded_braces' => true,
            'no_unneeded_control_parentheses' => true,
            'no_unneeded_import_alias' => true,
            'no_unneeded_final_method' => true, // risky
            'no_unreachable_default_argument_value' => true, // risky
            'no_unused_imports' => true,
            'no_useless_concat_operator' => true,
            'no_useless_return' => true,
            'no_whitespace_before_comma_in_array' => true,
            'normalize_index_brace' => true,
            'nullable_type_declaration' => ['syntax' => 'question_mark'],
            'nullable_type_declaration_for_default_null_value' => true,
            'object_operator_without_whitespace' => true,
            'ordered_imports' => ['imports_order' => ['class', 'function', 'const']],
            'ordered_class_elements' => [
                'order' => [
                    'use_trait',
                    'constant',
                    'case', // for enums only
                    'property',
                    'method',
                ],
            ],
            'php_unit_attributes' => true,
            'php_unit_construct' => true, // risky
            'php_unit_dedicate_assert' => ['target' => 'newest'], // risky
            'php_unit_expectation' => true, // risky
            'php_unit_fqcn_annotation' => true,
            'php_unit_method_casing' => ['case' => 'snake_case'],
            'php_unit_no_expectation_annotation' => true, // risky
            'php_unit_set_up_tear_down_visibility' => true, // risky
            'php_unit_strict' => true, // risky
            'php_unit_test_annotation' => ['style' => 'annotation'], // risky
            'phpdoc_align' => ['align' => 'left'],
            'phpdoc_array_type' => true, // risky
            'phpdoc_indent' => true,
            'phpdoc_line_span' => [
                'case' => 'single',
                'class' => 'single',
                'const' => 'single',
                'method' => 'single',
                'other' => 'single',
                'property' => 'single',
                'trait_import' => 'single',
                'function' => 'single',
            ],
            'phpdoc_param_order' => true,
            'phpdoc_scalar' => true,
            'phpdoc_single_line_var_spacing' => true,
            'phpdoc_tag_casing' => true,
            'phpdoc_types' => true,
            'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
            'protected_to_private' => true,
            'psr_autoloading' => true, // risky
            'self_accessor' => true, // risky
            'self_static_accessor' => true,
            'single_line_comment_spacing' => true,
            'single_line_comment_style' => ['comment_types' => ['asterisk', 'hash']],
            'single_quote' => true,
            'space_after_semicolon' => true,
            'standardize_not_equals' => true,
            // 'static_lambda' is deliberately NOT enabled: Laravel rebinds closures via Closure::bind (macros, Blade, Pest), static closures break there.
            // The report-only SlevomatCodingStandard.Functions.StaticClosure sniff enforces the same policy safely.
            'strict_param' => true, // risky
            'ternary_to_elvis_operator' => true, // risky
            'ternary_to_null_coalescing' => true,
            'trim_array_spaces' => true,
            // Deliberately narrower than PER-CS 2.0+ (arrays only, not arguments/parameters/match):
            // PHPCS forbids trailing commas in calls and declarations via SlevomatCodingStandard.Functions.DisallowTrailingComma* sniffs.
            'trailing_comma_in_multiline' => true,
            'type_declaration_spaces' => true,
            'types_spaces' => ['space' => 'single'],
            'whitespace_after_comma_in_array' => true,
            'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        ];
    }
    // phpcs:enable SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
}
