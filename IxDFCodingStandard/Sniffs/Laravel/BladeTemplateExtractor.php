<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Laravel;

use BadMethodCallException;

/** phpcs:disable IxDFCodingStandard.Laravel.NonExistingBladeTemplate.TemplateNotFound */
final class BladeTemplateExtractor
{
    private const INVALID_METHOD_CALL = 'Invalid method call';

    // @include, @component, @extends
    private const INCLUDE_BLADE_DIRECTIVE = '/@(include|component|extends)\(\'([^\']++)\'/';

    // @includeIf, @includeWhen
    private const CONDITIONAL_INCLUDE_BLADE_DIRECTIVE = '/@(includeIf|includeWhen)\([^,]++,\s*+\'([^\']++)\'/';

    // @includeFirst, @componentFirst - takes array parameter: @includeFirst(['view1', 'view2'])
    private const FIRST_BLADE_DIRECTIVE = '/@(includeFirst|componentFirst)\(\s*+\[\s*+\'([^\']++)\'/';

    // @each directive: @each('view.name', $items, 'item', 'empty.view')
    private const EACH_BLADE_DIRECTIVE = '/@each\(\s*+\'([^\']++)\'/';

    public function isBladeIncludeDirective(string $tokenContent): bool
    {
        return preg_match(self::INCLUDE_BLADE_DIRECTIVE, $tokenContent) === 1;
    }

    public function isConditionalBladeIncludeDirective(string $tokenContent): bool
    {
        return preg_match(self::CONDITIONAL_INCLUDE_BLADE_DIRECTIVE, $tokenContent) === 1;
    }

    public function isEachBladeDirective(string $tokenContent): bool
    {
        return preg_match(self::EACH_BLADE_DIRECTIVE, $tokenContent) === 1;
    }

    public function isFirstBladeDirective(string $tokenContent): bool
    {
        return preg_match(self::FIRST_BLADE_DIRECTIVE, $tokenContent) === 1;
    }

    public function getBladeTemplateName(string $tokenContent): string
    {
        if (!$this->isBladeIncludeDirective($tokenContent)) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        $matches = [];
        preg_match(self::INCLUDE_BLADE_DIRECTIVE, $tokenContent, $matches);

        if (!isset($matches[2])) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        return $matches[2];
    }

    public function getConditionalBladeTemplateName(string $tokenContent): string
    {
        if (!$this->isConditionalBladeIncludeDirective($tokenContent)) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        $matches = [];
        preg_match(self::CONDITIONAL_INCLUDE_BLADE_DIRECTIVE, $tokenContent, $matches);

        if (!isset($matches[2])) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        return $matches[2];
    }

    public function getEachBladeTemplateName(string $tokenContent): string
    {
        if (!$this->isEachBladeDirective($tokenContent)) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        $matches = [];
        preg_match(self::EACH_BLADE_DIRECTIVE, $tokenContent, $matches);

        if (!isset($matches[1])) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        return $matches[1];
    }

    public function getFirstBladeTemplateName(string $tokenContent): string
    {
        if (!$this->isFirstBladeDirective($tokenContent)) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        $matches = [];
        preg_match(self::FIRST_BLADE_DIRECTIVE, $tokenContent, $matches);

        if (!isset($matches[2])) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        return $matches[2];
    }
}
