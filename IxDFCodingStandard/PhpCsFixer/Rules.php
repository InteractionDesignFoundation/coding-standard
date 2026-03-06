<?php declare(strict_types=1);

namespace IxDFCodingStandard\PhpCsFixer;

/**
 * Shared PHP-CS-Fixer rules for IxDF projects.
 * @see https://mlocati.github.io/php-cs-fixer-configurator/
 */
final class Rules
{
    // phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
    /** @var array<string, array<string, mixed>|bool>|null */
    private static ?array $rules = null;

    // phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
    /** @return array<string, array<string, mixed>|bool> */
    public static function get(): array
    {
        return self::$rules ??= require dirname(__DIR__, 2).'/.php-cs-fixer-rules.php';
    }
}
