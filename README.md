[![Tests](https://github.com/InteractionDesignFoundation/coding-standard/actions/workflows/test.yml/badge.svg)](https://github.com/InteractionDesignFoundation/coding-standard/actions/workflows/test.yml)
[![PHP Psalm](https://github.com/InteractionDesignFoundation/coding-standard/actions/workflows/psalm.yml/badge.svg)](https://github.com/InteractionDesignFoundation/coding-standard/actions/workflows/psalm.yml)
[![PHP Psalm Level](https://shepherd.dev/github/InteractionDesignFoundation/coding-standard/level.svg)](https://shepherd.dev/github/InteractionDesignFoundation/coding-standard)
[![PHP Psalm Type Coverage](https://shepherd.dev/github/InteractionDesignFoundation/coding-standard/coverage.svg)](https://shepherd.dev/github/InteractionDesignFoundation/coding-standard)

# IxDF Coding Standard

An opinionated coding standard for PHP/Laravel projects. The two tools play different roles and are meant to run together:

- **PHP-CS-Fixer** (primary) — shared config based on the [latest PER Coding Style](https://www.php-fig.org/per/coding-style/) (currently PER-CS 3.0), plus formatting and modernization rules. It owns all code formatting and auto-fixes it.
- **PHP_CodeSniffer** (supplementary) — adds the structural and semantic checks PHP-CS-Fixer cannot enforce (strict types, Laravel conventions, naming, complexity). Formatting rules already covered by PHP-CS-Fixer are intentionally **not** duplicated here, so PHP_CodeSniffer is not a complete standard on its own.

## Installation

```shell
composer require --dev interaction-design-foundation/coding-standard
```

## PHP-CS-Fixer

Create `.php-cs-fixer.php` in your project root:

```php
<?php declare(strict_types=1);

use IxDFCodingStandard\PhpCsFixer\Config;

return Config::create(__DIR__);
```

`Config::create()` ships a sensible default Finder and enables parallel runs, risky rules, and caching. Two optional arguments let you adjust it:

```php
// Override individual rules.
return Config::create(__DIR__, ruleOverrides: [
    'final_public_method_for_abstract_class' => false,
]);
```

```php
// Provide your own Finder and use your cache path.
use PhpCsFixer\Finder;

$finder = Finder::create()->in(__DIR__)->name('*.php');

return Config::create(__DIR__, finder: $finder)
    ->setCacheFile($projectDir.'/.cache/.php-cs-fixer.cache');
```

Need only the rules array (e.g. to compose your own config)?

```php
$rules = \IxDFCodingStandard\PhpCsFixer\Rules::get();
```

Run it:

```shell
vendor/bin/php-cs-fixer fix --dry-run --diff   # check
vendor/bin/php-cs-fixer fix                     # fix
```

## PHP_CodeSniffer

Create `phpcs.xml` in your project root:

```xml
<?xml version="1.0"?>
<ruleset name="My Coding Standard">
    <rule ref="IxDFCodingStandard"/>
    <file>app</file>
    <file>config</file>
    <file>database</file>
    <file>routes</file>
    <file>tests</file>
</ruleset>
```

Run it:

```shell
vendor/bin/phpcs    # check
vendor/bin/phpcbf   # fix
```

## Composer scripts (recommended)

Wire both tools into `composer.json` so the whole team runs them the same way:

```json
"scripts": {
    "cs:check": ["@php-cs-fixer:dry", "@phpcs"],
    "cs:fix": ["@php-cs-fixer", "@phpcbf"],
    "phpcs": "phpcs -p -s --colors --report-full --report-summary",
    "phpcbf": "phpcbf -p --colors",
    "php-cs-fixer": "php-cs-fixer fix --no-interaction --ansi --quiet",
    "php-cs-fixer:dry": "php-cs-fixer fix --no-interaction --ansi --verbose --dry-run"
}
```

```shell
composer cs:check   # check with both tools
composer cs:fix     # fix with both tools
```
