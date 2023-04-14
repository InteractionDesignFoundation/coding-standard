<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Laravel;

use BadMethodCallException;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class NonExistingBladeTemplateSniff implements Sniff
{
    private const INVALID_METHOD_CALL = 'Invalid method call';

    // @include
    private const INCLUDE_BLADE_DIRECTIVE = '/@(include|component|extends)\(\'([^\']++)\'/';

    // @includeIf
    private const CONDITIONAL_INCLUDE_BLADE_DIRECTIVE = '/@(includeIf|includeWhen)\([^,]++,\s*+\'([^\']++)\'/';

    /** @var array<string, bool> */
    private array $checkedFiles = [];

    /** @var array<string, non-empty-string> */
    public array $templatePaths = [];

    /** @inheritDoc */
    public function register(): array
    {
        return [\T_OPEN_TAG, \T_INLINE_HTML];
    }

    /** @inheritDoc */
    public function process(File $phpcsFile, $stackPtr): int // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    {
        $tokens = $phpcsFile->getTokens();

        $filename = $phpcsFile->getFilename();

        $hash = md5($filename);
        if (($this->checkedFiles[$hash] ?? false) || $filename === 'STDIN' || \str_contains($filename, '.stub')) {
            return 0;
        }

        $this->checkedFiles[$hash] = true;

        foreach ($tokens as $position => $token) {
            if ($this->isBladeIncludeDirective($token['content'])) {
                $this->validateTemplateName($this->getBladeTemplateName($token['content']), $phpcsFile, $position);
            } elseif ($this->isConditionalBladeIncludeDirective($token['content'])) {
                $this->validateTemplateName($this->getConditionalBladeTemplateName($token['content']), $phpcsFile, $position);
            } elseif ($this->isViewFacade($tokens, $position)) {
                $this->validateTemplateName($this->getViewFacadeTemplateName($tokens, $position), $phpcsFile, $position);
            } elseif ($this->isViewFunctionFactory($tokens, $position)) {
                $this->validateTemplateName($this->getViewFunctionFactoryTemplateName($tokens, $position), $phpcsFile, $position);
            } elseif ($this->isViewFunction($tokens, $position)) {
                $this->validateTemplateName($this->getViewFunctionTemplateName($tokens, $position), $phpcsFile, $position);
            }
        }

        return 0;
    }

    private function isBladeIncludeDirective(string $tokenContent): bool
    {
        return preg_match(self::INCLUDE_BLADE_DIRECTIVE, $tokenContent) === 1;
    }

    private function isConditionalBladeIncludeDirective(string $tokenContent): bool
    {
        return preg_match(self::CONDITIONAL_INCLUDE_BLADE_DIRECTIVE, $tokenContent) === 1;
    }

    private function getBladeTemplateName(string $tokenContent): string
    {
        if (!$this->isBladeIncludeDirective($tokenContent)) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        $matches = [];
        preg_match(self::INCLUDE_BLADE_DIRECTIVE, $tokenContent, $matches);

        if (!isset($matches[2])) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        return (string) $matches[2];
    }

    private function getConditionalBladeTemplateName(string $tokenContent): string
    {
        if (!$this->isConditionalBladeIncludeDirective($tokenContent)) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        $matches = [];
        preg_match(self::CONDITIONAL_INCLUDE_BLADE_DIRECTIVE, $tokenContent, $matches);

        if (!isset($matches[2])) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        return (string) $matches[2];
    }

    private function templateIsMissing(string $templateName): bool
    {
        return ! file_exists($this->getTemplatePath($templateName));
    }

    private function getTemplatePath(string $name): string
    {
        $namespace = preg_match('/^(\w++)::/', $name, $matches)
            ? $matches[1]
            : '';

        if (! isset($this->templatePaths[$namespace])) {
            throw new \InvalidArgumentException("Unable to find namespace {$namespace} in configurations!");
        }

        $withoutNamespace = ! empty($namespace)
            ? str_replace("$namespace::", '', $name)
            : $name;

        return sprintf(
            '%s/%s/%s.blade.php',
            dirname(__DIR__, 6),
            $this->templatePaths[$namespace],
            str_replace('.', '/', $withoutNamespace)
        );
    }

    private function reportMissingTemplate(File $phpcsFile, int $stackPtr, string $templateName): void
    {
        $phpcsFile->addWarning(
            'Template "%s" (%s) does not exist in "%s"',
            $stackPtr,
            'TemplateNotFound',
            [$templateName, $this->getTemplatePath($templateName), $phpcsFile->getFilename()]
        );
    }

    private function validateTemplateName(string $templateName, File $phpcsFile, int $stackPtr): void
    {
        if (rtrim($templateName, '-_.') !== $templateName) {
            return;
        }

        if (str_contains($templateName, '$')) {
            return;
        }

        if ($this->templateIsMissing($templateName)) {
            $this->reportMissingTemplate($phpcsFile, $stackPtr, $templateName);
        }
    }

    /** @param array<array<string>> $tokens */
    private function isViewFacade(array $tokens, int $position): bool
    {
        return isset($tokens[$position + 2]) &&
            ($tokens[$position]['content'] === 'View' || $tokens[$position]['content'] === 'ViewFacade') &&
            $tokens[$position + 1]['type'] === 'T_DOUBLE_COLON' &&
            $tokens[$position + 2]['content'] === 'make' &&
            $tokens[$position + 3]['type'] === 'T_CONSTANT_ENCAPSED_STRING';
    }

    /** @param array<array<string>> $tokens */
    private function getViewFacadeTemplateName(array $tokens, int $position): string // phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    {
        if (! $this->isViewFacade($tokens, $position)) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        $lookupPosition = $position + 4;
        do {
            if ($tokens[$lookupPosition]['type'] !== 'T_WHITESPACE') {
                return trim($tokens[$lookupPosition]['content'], '\'');
            }

            $lookupPosition++;
        } while (isset($tokens[$lookupPosition]) && $lookupPosition < $position + 14);

        throw new BadMethodCallException('Unable to find the template name');
    }

    /** @param array<array<string>> $tokens */
    private function isViewFunctionFactory(array $tokens, int $position): bool
    {
        return isset($tokens[$position + 4]) &&
            $tokens[$position]['content'] === 'view' &&
            $tokens[$position + 1]['content'] === '(' &&
            $tokens[$position + 2]['content'] === ')' &&
            $tokens[$position + 3]['content'] === '->' &&
            $tokens[$position + 4]['content'] === 'make' &&
            $tokens[$position + 6]['type'] !== 'T_VARIABLE';
    }

    /** @param array<array<string>> $tokens */
    private function getViewFunctionFactoryTemplateName(array $tokens, int $position): string // phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    {
        if (! $this->isViewFunctionFactory($tokens, $position)) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        $lookupPosition = $position + 6;
        do {
            if ($tokens[$lookupPosition]['type'] !== 'T_WHITESPACE') {
                return trim($tokens[$lookupPosition]['content'], '\'');
            }

            $lookupPosition++;
        } while (isset($tokens[$lookupPosition]) && $lookupPosition < $position + 16);

        throw new BadMethodCallException('Unable to find the template name');
    }

    /** @param array<array<string>> $tokens */
    private function isViewFunction(array $tokens, int $position): bool
    {
        return isset($tokens[$position - 1], $tokens[$position + 4]) &&
            $tokens[$position - 1]['type'] === 'T_WHITESPACE' &&
            $tokens[$position]['content'] === 'view' &&
            $tokens[$position + 1]['content'] === '(' &&
            $tokens[$position + 2]['type'] === 'T_CONSTANT_ENCAPSED_STRING';
    }

    /** @param array<array<string>> $tokens */
    private function getViewFunctionTemplateName(array $tokens, int $position): string
    {
        if (! $this->isViewFunction($tokens, $position)) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        return trim($tokens[$position + 2]['content'], '\'');
    }
}
