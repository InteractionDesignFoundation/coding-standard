<?php declare(strict_types=1);

namespace IxDFCodingStandard\PhpCsFixer;

use PhpCsFixer\Config as BaseConfig;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

/**
 * Pre-configured PHP-CS-Fixer config factory for IxDF projects.
 * @see README.md for usage examples
 * @api
 */
final class Config
{
    // phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
    /** @param array<string, array<string, mixed>|bool> $ruleOverrides Rules to merge on top of the shared ruleset */
    public static function create(string $projectDir, array $ruleOverrides = [], ?Finder $finder = null): BaseConfig
    {
        $finder ??= self::finder($projectDir);

        $config = new BaseConfig();
        $config
            ->setParallelConfig(ParallelConfigFactory::detect())
            ->setUsingCache(true)
            ->setRiskyAllowed(true)
            ->setIndent('    ')
            ->setLineEnding("\n")
            ->setRules(array_merge(Rules::get(), $ruleOverrides))
            ->setFinder($finder);

        return $config;
    }

    /**
     * Default Finder; extend it rather than building one from scratch, so the excludes below still apply.
     * PhpCsFixer\Finder lints gitignored files, and only exclude() prunes the walk (ignoreVCSIgnored() filters after descending).
     */
    public static function finder(string $projectDir): Finder
    {
        return Finder::create()
            ->in($projectDir)
            ->exclude([
                '.cache',
                '.docker',
                'bootstrap/cache',
                'node_modules',
                'storage',
                'vendor',
            ])
            ->name('*.php')
            ->notName('*.blade.php')
            ->notName('_ide_helper.php')
            ->notName('.phpstorm.meta.php')
            ->ignoreDotFiles(false)
            ->ignoreVCS(true)
            ->ignoreVCSIgnored(true);
    }
}
