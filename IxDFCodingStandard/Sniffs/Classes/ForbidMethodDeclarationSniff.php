<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Classes;

use IxDFCodingStandard\Helpers\ClassHelper;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class ForbidMethodDeclarationSniff implements Sniff
{
    public const FORBIDDEN_METHOD_DECLARATION = 'ForbiddenMethodDeclaration';

    /**
     * A list of forbidden to declare methods.
     * @var array<string, string>
     */
    public $forbiddenMethods = [];

    /** @return list<int> */
    public function register(): array
    {
        return [
            \T_CLASS,
        ];
    }

    /** @param int $classPointer */
    public function process(File $phpcsFile, $classPointer): void
    {
        /** @var class-string $fqcn */
        $fqcn = ClassHelper::getFullyQualifiedName($phpcsFile, $classPointer);
        foreach ($this->forbiddenMethods as $typeAndMethod => $replacement) {
            [$type, $method] = explode('::', $typeAndMethod);

            if (! $this->declaresForbiddenMethod($fqcn, $type, $method)) {
                continue;
            }

            $phpcsFile->addError(
                sprintf('Method “%s” is forbidden, use “%s” instead.', $typeAndMethod, $replacement),
                $classPointer,
                self::FORBIDDEN_METHOD_DECLARATION
            );
        }
    }

    /** @param class-string $fqcn */
    private function declaresForbiddenMethod(string $fqcn, string $type, string $method): bool
    {
        // is_subclass_of()/method_exists() autoload $fqcn. The analysed class may use a deprecated trait
        // or extend a deprecated class, which emits a deprecation on PHP 8.5+ (e.g. "Trait X used by Y is
        // deprecated"). That deprecation is a side effect of the analysed code, not of this sniff, and would
        // otherwise be turned into an exception that aborts the whole file check, so it is silenced here.
        set_error_handler(static fn(): bool => true, \E_DEPRECATED | \E_USER_DEPRECATED);

        try {
            return is_subclass_of($fqcn, $type) && method_exists($fqcn, $method);
        } finally {
            restore_error_handler();
        }
    }
}
