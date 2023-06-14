<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class ForbidDirectClassInheritanceSniff implements Sniff
{
    public const FORBIDDEN_CLASS_INHERITED = 'ForbiddenInheritance';

    /**
     * A list of forbidden classes not allowed to inherit directly.
     * Usually used to force developers to use our custom wrappers instead of framework or library functionality.
     * @var array<class-string, class-string|null>
     */
    public $forbiddenClasses = [];

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
        $parentFQCN = $phpcsFile->findExtendedClassName($classPointer);

        if ($parentFQCN === false || ! array_key_exists($parentFQCN, $this->forbiddenClasses)) {
            return;
        }

        $replaceBy = $this->forbiddenClasses[$parentFQCN];
        $error = sprintf(
            'Class “%s” is forbidden for direct inheritance, “%s” should be used instead.',
            $parentFQCN,
            $replaceBy
        );
        $phpcsFile->addError($error, $classPointer, self::FORBIDDEN_CLASS_INHERITED);
    }
}
