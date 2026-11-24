<?php declare(strict_types=1);

use IxDFCodingStandard\PhpCsFixer\Config;

// Sniff test fixtures are intentionally malformed; reformatting them would shift line numbers and break the sniff tests.
$finder = Config::finder(__DIR__)->notPath('#^tests/Sniffs/#');

// mb_str_functions targets user-facing application strings. This package only processes ASCII PHP tokens,
// so keep the plain byte-string functions and avoid pulling in an ext-mbstring runtime dependency.
return Config::create(__DIR__, finder: $finder, ruleOverrides: ['mb_str_functions' => false])
    ->setCacheFile('./.cache/.php-cs-fixer.cache');
