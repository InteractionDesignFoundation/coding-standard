[![Tests](https://github.com/InteractionDesignFoundation/coding-standard/actions/workflows/test.yml/badge.svg)](https://github.com/InteractionDesignFoundation/coding-standard/actions/workflows/test.yml)
[![PHP Psalm](https://github.com/InteractionDesignFoundation/coding-standard/actions/workflows/psalm.yml/badge.svg)](https://github.com/InteractionDesignFoundation/coding-standard/actions/workflows/psalm.yml)
[![PHP Psalm Level](https://shepherd.dev/github/InteractionDesignFoundation/coding-standard/level.svg)](https://shepherd.dev/github/InteractionDesignFoundation/coding-standard)
[![PHP Psalm Type Coverage](https://shepherd.dev/github/InteractionDesignFoundation/coding-standard/coverage.svg)](https://shepherd.dev/github/InteractionDesignFoundation/coding-standard)

# IxDF PHP Coding Standard

An opinionated coding standard for PHP and Laravel projects, built on [PER-CS 3.0](https://www.php-fig.org/per/coding-style/).
Focuses on:
 - **High signal-to-noise ratio** (concise but informative PHPDoc, e.g. array shapes)
 - **Harmony with static analysis tools** (PHPStan, Psalm, Rector, etc.)
 - **Auto-fixing** (violations are not only reported but fixed automatically, when possible)

Two tools, two roles, meant to run together:
- **PHP-CS-Fixer** (primary): fast PER-CS 3+ auto-formatting and modernization rules.
- **PHP_CodeSniffer** (supplementary): structural and semantic checks PHP-CS-Fixer cannot express (strict types, Laravel conventions, naming, complexity).

> [!TIP]
> This repository is a part of the IxDF toolchain for PHP: Rector ➝ Coding Standard Fixers ➝ PHPStan + Psalm.

## 1. Installation

```shell
composer require --dev interaction-design-foundation/coding-standard
```
## 2. Configuration

### PHP-CS-Fixer

Create `.php-cs-fixer.php` in your project root:

```php
<?php declare(strict_types=1);

use IxDFCodingStandard\PhpCsFixer\Config;

// @see https://mlocati.github.io/php-cs-fixer-configurator/ for ruleOverrides options.
return Config::create(__DIR__, ruleOverrides: []);
```

<details>
<summary>Customisation (optional)</summary>
<code>Config::create()</code> ships a sensible default Finder and enables parallel runs, risky rules, and caching.
You can customize it by using your Finder instance and by overriding individual rules:

```php
use PhpCsFixer\Finder;

$finder = Finder::create()->in(__DIR__)->name('*.php');

// Override individual rules, your own Finder and use your cache path
return Config::create(__DIR__, finder: $finder, ruleOverrides: [
    'final_public_method_for_abstract_class' => false,
])->setCacheFile('./.cache/.php-cs-fixer.cache');
```

Need only the rule array (e.g. to compose your own config)?

```php
$rules = \IxDFCodingStandard\PhpCsFixer\Rules::get();
```
</details>

Run it:

```shell
vendor/bin/php-cs-fixer fix
```

### PHP_CodeSniffer

Create `phpcs.xml` in your project root:

```xml
<?xml version="1.0"?>
<ruleset name="My Coding Standard">
    <file>app</file>
    <file>config</file>
    <file>database</file>
    <file>routes</file>
    <file>tests</file>

    <rule ref="IxDFCodingStandard"/>
</ruleset>
```

On top of the Generic, PSR and Slevomat rulesets, `IxDFCodingStandard` ships its own sniffs.
Some run by default; some are opt-in. See [docs/README.md](docs/README.md) for the full list and configuration.

Run it:

```shell
vendor/bin/phpcbf   # fix
vendor/bin/phpcs    # check (dry-run)
```

### Composer scripts (recommended)

Wire both tools into `composer.json` so the whole team runs them the same way:

```shell
composer cs
```

`cs` fixes files in place. Edit `composer.json`:

```json
{
    "scripts": {
        "cs": ["@php-cs-fixer", "@phpcbf"],
        "phpcbf": "phpcbf -p",
        "phpcs": "phpcs -p -s --report-full --report-summary",
        "php-cs-fixer": "php-cs-fixer fix --no-interaction --ansi --quiet"
    }
}
```

## 3. Continuous integration

Run the fixer in CI and commit the result back, so the branch is always formatted without blocking the build.
This repository dogfoods that pattern; copy its workflow as a starting point: [`.github/workflows/coding-standard.yml`](.github/workflows/coding-standard.yml).
