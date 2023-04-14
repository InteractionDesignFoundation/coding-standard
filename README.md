# IxDF Coding Standard for Laravel

An opinoned ruleset focused on strict types.
Suitable for both applications and packages.


## Installation

1. Install the package via composer by running:
```shell
composer require --dev interaction-design-foundation/coding-standard
```

2. Add composer scripts into your `composer.json`:
```json
"scripts": {
  "cs-check": "phpcs",
  "cs-fix": "phpcbf"
}
```

3. Create file phpcs.xml on base path of your repository with content
```xml
<?xml version="1.0"?>
<ruleset name="My Coding Standard">
    <!-- Include all rules from the IxDF Coding Standard -->
    <rule ref="IxDFCodingStandard"/>

    <!-- Paths to check -->
    <file>app</file>
    <file>config</file>
    <file>database</file>
    <file>lang</file>
    <file>routes</file>
    <file>tests</file>
</ruleset>
```

## Usage

- To run checks only:

```shell
composer cs-check
```

- To automatically fix many CS issues:

```shell
composer cs-fix
```

## Ignoring parts of a File

Disable parts of a file:

```php
$xmlPackage = new XMLPackage;
// phpcs:disable
$xmlPackage['error_code'] = get_default_error_code_value();
$xmlPackage->send();
// phpcs:enable
```

Disable a specific rule:

```php
// phpcs:disable Generic.Commenting.Todo.Found
$xmlPackage = new XMLPackage;
$xmlPackage['error_code'] = get_default_error_code_value();
// TODO: Add an error message here.
$xmlPackage->send();
// phpcs:enable
```

Ignore a specific violation:

```php
$xmlPackage = new XMLPackage;
$xmlPackage['error_code'] = get_default_error_code_value();
// phpcs:ignore Generic.Commenting.Todo.Found
// TODO: Add an error message here.
$xmlPackage->send();
```

## Development

### Versioning
> **New rules or Sniffs may not be introduced in minor or bugfix releases and should always be based on the develop
branch and queued for the next major release, unless considered a bugfix for existing rules.**


## Reference

Rules can be added, excluded or tweaked locally, depending on your preferences.
More information on how to do this can be found here:

- [Coding Standard Tutorial](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Coding-Standard-Tutorial)
- [Configuration Options](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Configuration-Options)
- [Selectively Applying Rules](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Annotated-Ruleset#selectively-applying-rules)
- [Customisable Sniff Properties](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Customisable-Sniff-Properties)
- Other coding standards (inspiring us):
  - [Slevomat coding standard](https://github.com/slevomat/coding-standard)
  - [Doctrine coding standard](https://github.com/doctrine/coding-standard)
  - [Laminas coding standard](https://github.com/laminas/laminas-coding-standard)
