[![Tests](https://github.com/InteractionDesignFoundation/coding-standard/actions/workflows/test.yml/badge.svg)](https://github.com/InteractionDesignFoundation/coding-standard/actions/workflows/test.yml)
[![PHP Psalm](https://github.com/InteractionDesignFoundation/coding-standard/actions/workflows/psalm.yml/badge.svg)](https://github.com/InteractionDesignFoundation/coding-standard/actions/workflows/psalm.yml)
[![PHP Psalm Level](https://shepherd.dev/github/InteractionDesignFoundation/coding-standard/level.svg)](https://shepherd.dev/github/InteractionDesignFoundation/coding-standard)
[![PHP Psalm Type Coverage](https://shepherd.dev/github/InteractionDesignFoundation/coding-standard/coverage.svg)](https://shepherd.dev/github/InteractionDesignFoundation/coding-standard)

# IxDF Coding Standard

An opinionated coding standard for PHP/Laravel projects. Provides two independent tools — use either one or both together:

- **PHP_CodeSniffer** — custom sniffs for strict types and Laravel conventions
- **PHP-CS-Fixer** — shared config with 80+ rules based on PER-CS 3.0

## Installation

```shell
composer require --dev interaction-design-foundation/coding-standard
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

## PHP-CS-Fixer

Create `.php-cs-fixer.php` in your project root:

```php
<?php declare(strict_types=1);

use IxDFCodingStandard\PhpCsFixer\Config;

return Config::create(__DIR__);
```

With rule overrides:

```php
return Config::create(__DIR__, ruleOverrides: [
    'final_public_method_for_abstract_class' => false,
]);
```

With a custom Finder:

```php
use IxDFCodingStandard\PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()->in(__DIR__)->name('*.php');

return Config::create(__DIR__, finder: $finder);
```

If you only need the rules array:
```php
$rules = \IxDFCodingStandard\PhpCsFixer\Rules::get();
```

## Usage

```shell
composer cs:check   # check only
composer cs:fix     # auto-fix
```
