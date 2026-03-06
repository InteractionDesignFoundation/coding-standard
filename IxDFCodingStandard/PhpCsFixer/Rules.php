<?php declare(strict_types=1);

namespace IxDFCodingStandard\PhpCsFixer;

/**
 * Shared PHP-CS-Fixer rules for IxDF projects.
 *
 * @see https://mlocati.github.io/php-cs-fixer-configurator/
 */
final class Rules
{
    /** @var array<string, mixed>|null */
    private static ?array $rules = null;

    /** @return array<string, mixed> */
    public static function get(): array
    {
        return self::$rules ??= require dirname(__DIR__, 2).'/.php-cs-fixer-rules.php';
    }
}
