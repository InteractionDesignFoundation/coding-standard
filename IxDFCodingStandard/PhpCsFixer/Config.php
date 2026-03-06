<?php declare(strict_types=1);

namespace IxDFCodingStandard\PhpCsFixer;

use PhpCsFixer\Config as BaseConfig;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

/**
 * Pre-configured PHP-CS-Fixer config factory for IxDF projects.
 *
 * @see README.md for usage examples
 */
final class Config
{
    /**
     * @param array<string, mixed> $ruleOverrides Rules to merge on top of the shared ruleset
     */
    public static function create(
        string $projectDir,
        array $ruleOverrides = [],
        ?Finder $finder = null,
    ): BaseConfig {
        $finder ??= self::defaultFinder($projectDir);

        return (new BaseConfig())
            ->setParallelConfig(ParallelConfigFactory::detect())
            ->setUsingCache(true)
            ->setCacheFile($projectDir.'/.cache/.php-cs-fixer.cache')
            ->setRiskyAllowed(true)
            ->setIndent('    ')
            ->setLineEnding("\n")
            ->setRules(array_merge(Rules::get(), $ruleOverrides))
            ->setFinder($finder);
    }

    private static function defaultFinder(string $projectDir): Finder
    {
        return Finder::create()
            ->in($projectDir)
            ->exclude([
                '.cache',
                '.docker',
                'bootstrap/cache',
                'node_modules',
                'public',
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
