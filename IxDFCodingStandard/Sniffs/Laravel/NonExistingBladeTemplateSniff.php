<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Laravel;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class NonExistingBladeTemplateSniff implements Sniff
{
    private const INVALID_METHOD_CALL = 'Invalid method call';

    public const CODE_TEMPLATE_NOT_FOUND = 'TemplateNotFound';
    public const CODE_UNKNOWN_VIEW_NAMESPACE = 'UnknownViewNamespace';

    // @include
    private const INCLUDE_BLADE_DIRECTIVE = '/@(include|component|extends)\(\'([^\']++)\'/';

    // @includeIf
    private const CONDITIONAL_INCLUDE_BLADE_DIRECTIVE = '/@(includeIf|includeWhen)\([^,]++,\s*+\'([^\']++)\'/';

    /** @var list<non-empty-string> The same as for config('view.paths') */
    public array $viewPaths = [
        'resources/views',
    ];

    /** @var array<non-empty-string, non-empty-string> Example: <element key="googletagmanager" value="resources/views/vendor/googletagmanager"/> */
    public array $viewNamespaces = [];

    /** @var non-empty-string|null Custom base directory, defaults to auto-detection */
    public ?string $baseDir = null;

    /** @var array<string, bool> */
    private array $checkedFiles = [];

    /** @inheritDoc */
    public function register(): array
    {
        return [\T_OPEN_TAG, \T_INLINE_HTML, \T_STRING];
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
            $tokenContent = $token['content'];

            // Handle Blade directives (found in T_INLINE_HTML tokens)
            if ($this->isBladeIncludeDirective($tokenContent)) {
                $this->validateTemplateName($this->getBladeTemplateName($tokenContent), $phpcsFile, $position);
            } elseif ($this->isConditionalBladeIncludeDirective($tokenContent)) {
                $this->validateTemplateName($this->getConditionalBladeTemplateName($tokenContent), $phpcsFile, $position);
            }
            // Handle PHP code (found in T_STRING tokens)
            elseif ($token['type'] === 'T_STRING') {
                if ($this->isViewFacade($tokens, $position)) {
                    $this->validateTemplateName($this->getViewFacadeTemplateName($tokens, $position), $phpcsFile, $position);
                } elseif ($this->isViewFunctionFactory($tokens, $position)) {
                    $this->validateTemplateName($this->getViewFunctionFactoryTemplateName($tokens, $position), $phpcsFile, $position);
                } elseif ($this->isViewFunction($tokens, $position)) {
                    $this->validateTemplateName($this->getViewFunctionTemplateName($tokens, $position), $phpcsFile, $position);
                }
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
            throw new \BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        $matches = [];
        preg_match(self::INCLUDE_BLADE_DIRECTIVE, $tokenContent, $matches);

        if (!isset($matches[2])) {
            throw new \BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        return (string) $matches[2];
    }

    private function getConditionalBladeTemplateName(string $tokenContent): string
    {
        if (!$this->isConditionalBladeIncludeDirective($tokenContent)) {
            throw new \BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        $matches = [];
        preg_match(self::CONDITIONAL_INCLUDE_BLADE_DIRECTIVE, $tokenContent, $matches);

        if (!isset($matches[2])) {
            throw new \BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        return (string) $matches[2];
    }

    /**
     * @param string $templateName In dot notation
     * @throws \OutOfBoundsException
     */
    private function templateIsMissing(string $templateName): bool
    {
        foreach ($this->getTemplatePathCandidates($templateName) as $candidateFilePath) {
            if (\file_exists($candidateFilePath)) {
                return false;
            }
        }

        return true;
    }

    private function resolveLaravelBaseDir(): string
    {
        return $this->baseDir ?? dirname(__DIR__, 6); // assume this file in the classic vendor dir
    }

    /**
     * @param string $templatePath In dot notation
     * @return list<non-empty-string>
     * @throws \OutOfBoundsException
     */
    private function getTemplatePathCandidates(string $templatePath): array
    {
        /**
         * Here, we have 2 cases:
         * 1. No custom namespace, e.g. 'dashboard.index' -> resources/views/dashboard/index.blade.php
         * 2. Custom namespace, e.g. 'googletagmanager::head' -> resources/views/vendor/googletagmanager ({@see \Illuminate\Support\ServiceProvider::loadViewsFrom})
         */

        $hasCustomNamespace = preg_match('/^(\w++)::\w+/', $templatePath, $matches);

        // 2. Custom namespace
        if ($hasCustomNamespace) {
            $namespace = (string) $matches[1];

            if (!isset($this->viewNamespaces[$namespace])) {
                throw new \OutOfBoundsException("Unable to find view namespace “{$namespace}” in viewNamespaces property.");
            }

            $namespacePath = $this->viewNamespaces[$namespace];

            $templateNameWithoutNamespace = str_replace("{$namespace}::", '', $templatePath);

            return [
                sprintf(
                    '%s/%s/%s.blade.php',
                    $this->resolveLaravelBaseDir(),
                    $namespacePath,
                    str_replace('.', '/', $templateNameWithoutNamespace)
                ),
            ];
        }

        // 1. No custom namespace
        $candidates = [];
        foreach ($this->viewPaths as $viewPath) {
            $candidates[] = sprintf(
                '%s/%s/%s.blade.php',
                $this->resolveLaravelBaseDir(),
                $viewPath,
                str_replace('.', '/', $templatePath)
            );
        }

        return $candidates;
    }

    private function reportTemplateNotFound(File $phpcsFile, int $stackPtr, string $templateName): void
    {
        $phpcsFile->addError(
            'Blade template "%s" not found. Expected location(s): "%s"',
            $stackPtr,
            self::CODE_TEMPLATE_NOT_FOUND,
            [
                $templateName,
                implode(', ', $this->getTemplatePathCandidates($templateName)),
            ]
        );
    }

    private function reportUnknownViewNamespace(File $phpcsFile, int $stackPtr, string $templateName): void
    {
        $phpcsFile->addWarning(
            'Blade template namespace for the "%s" is not registered via "viewNamespaces" property.',
            $stackPtr,
            self::CODE_UNKNOWN_VIEW_NAMESPACE,
            [
                $templateName,
            ]
        );
    }

    private function validateTemplateName(string $templateName, File $phpcsFile, int $stackPtr): void
    {
        if (mb_rtrim($templateName, '-_.') !== $templateName) {
            return;
        }

        if (str_contains($templateName, '$')) {
            return;
        }

        try {
            if ($this->templateIsMissing($templateName)) {
                $this->reportTemplateNotFound($phpcsFile, $stackPtr, $templateName);
            }
        } catch (\OutOfBoundsException) {
            $this->reportUnknownViewNamespace($phpcsFile, $stackPtr, $templateName);
        }
    }

    /** @param array<array<string>> $tokens */
    private function isViewFacade(array $tokens, int $position): bool
    {
        if (!isset($tokens[$position + 3])) {
            return false;
        }

        return ($tokens[$position]['content'] === 'View' || $tokens[$position]['content'] === 'ViewFacade') &&
            $tokens[$position + 1]['type'] === 'T_DOUBLE_COLON' &&
            $tokens[$position + 2]['content'] === 'make' &&
            $tokens[$position + 3]['type'] === 'T_CONSTANT_ENCAPSED_STRING';
    }

    /** @param array<array<string>> $tokens */
    private function getViewFacadeTemplateName(array $tokens, int $position): string // phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    {
        if (!$this->isViewFacade($tokens, $position)) {
            throw new \BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        $maxLookupPosition = $position + 14;
        for ($lookupPosition = $position + 4; $lookupPosition < $maxLookupPosition && isset($tokens[$lookupPosition]); $lookupPosition++) {
            if ($tokens[$lookupPosition]['type'] !== 'T_WHITESPACE') {
                return mb_trim($tokens[$lookupPosition]['content'], '\'"');
            }
        }

        throw new \BadMethodCallException('Unable to find the template name');
    }

    /** @param array<array<string>> $tokens */
    private function isViewFunctionFactory(array $tokens, int $position): bool
    {
        if (!isset($tokens[$position + 6])) {
            return false;
        }

        return $tokens[$position]['content'] === 'view' &&
            $tokens[$position + 1]['content'] === '(' &&
            $tokens[$position + 2]['content'] === ')' &&
            $tokens[$position + 3]['content'] === '->' &&
            $tokens[$position + 4]['content'] === 'make' &&
            $tokens[$position + 6]['type'] !== 'T_VARIABLE';
    }

    /** @param array<array<string>> $tokens */
    private function getViewFunctionFactoryTemplateName(array $tokens, int $position): string // phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    {
        if (!$this->isViewFunctionFactory($tokens, $position)) {
            throw new \BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        $maxLookupPosition = $position + 16;
        for ($lookupPosition = $position + 6; $lookupPosition < $maxLookupPosition && isset($tokens[$lookupPosition]); $lookupPosition++) {
            if ($tokens[$lookupPosition]['type'] !== 'T_WHITESPACE') {
                return mb_trim($tokens[$lookupPosition]['content'], '\'"');
            }
        }

        throw new \BadMethodCallException('Unable to find the template name');
    }

    /** @param array<array<string>> $tokens */
    private function isViewFunction(array $tokens, int $position): bool
    {
        if (!isset($tokens[$position - 1], $tokens[$position + 2])) {
            return false;
        }

        return $tokens[$position - 1]['type'] === 'T_WHITESPACE' &&
            $tokens[$position]['content'] === 'view' &&
            $tokens[$position + 1]['content'] === '(' &&
            $tokens[$position + 2]['type'] === 'T_CONSTANT_ENCAPSED_STRING';
    }

    /** @param array<array<string>> $tokens */
    private function getViewFunctionTemplateName(array $tokens, int $position): string
    {
        if (!$this->isViewFunction($tokens, $position)) {
            throw new \BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        return mb_trim($tokens[$position + 2]['content'], '\'"');
    }
}
