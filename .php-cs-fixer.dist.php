<?php declare(strict_types=1);

use IxDFCodingStandard\PhpCsFixer\Config;
use PhpCsFixer\Finder;

// Dogfood: lint this package with its own shared PHP-CS-Fixer configuration.
$finder = Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor', '.cache'])
    // Sniff test fixtures are intentionally malformed; reformatting them would shift line numbers and break the sniff tests.
    ->notPath('#^tests/Sniffs/#')
    ->name('*.php');

// mb_str_functions targets user-facing application strings. This package only processes ASCII PHP tokens,
// so keep the plain byte-string functions and avoid pulling in an ext-mbstring runtime dependency.
return Config::create(__DIR__, ruleOverrides: ['mb_str_functions' => false], finder: $finder);
