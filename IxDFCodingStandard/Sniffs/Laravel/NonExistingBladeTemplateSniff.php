<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Laravel;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class NonExistingBladeTemplateSniff implements Sniff
{
    public const CODE_TEMPLATE_NOT_FOUND = 'TemplateNotFound';
    public const CODE_UNKNOWN_VIEW_NAMESPACE = 'UnknownViewNamespace';

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

    private BladeTemplateExtractor $bladeExtractor;
    private PhpViewExtractor $phpExtractor;

    public function __construct()
    {
        $this->bladeExtractor = new BladeTemplateExtractor();
        $this->phpExtractor = new PhpViewExtractor();
    }

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
            if ($this->bladeExtractor->isBladeIncludeDirective($tokenContent)) {
                $this->validateTemplateName($this->bladeExtractor->getBladeTemplateName($tokenContent), $phpcsFile, $position);
            } elseif ($this->bladeExtractor->isConditionalBladeIncludeDirective($tokenContent)) {
                $this->validateTemplateName($this->bladeExtractor->getConditionalBladeTemplateName($tokenContent), $phpcsFile, $position);
            } elseif ($this->bladeExtractor->isEachBladeDirective($tokenContent)) {
                $this->validateTemplateName($this->bladeExtractor->getEachBladeTemplateName($tokenContent), $phpcsFile, $position);
            } elseif ($this->bladeExtractor->isFirstBladeDirective($tokenContent)) {
                $this->validateTemplateName($this->bladeExtractor->getFirstBladeTemplateName($tokenContent), $phpcsFile, $position);
            } elseif ($token['type'] === 'T_STRING') { // Handle PHP code (found in T_STRING tokens)
                if ($this->phpExtractor->isViewFunction($tokens, $position)) {
                    $this->validateTemplateName($this->phpExtractor->getViewFunctionTemplateName($tokens, $position), $phpcsFile, $position);
                }
            }
        }

        return 0;
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
}
