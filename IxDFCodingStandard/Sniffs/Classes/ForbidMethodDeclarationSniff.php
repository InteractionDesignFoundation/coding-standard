<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\ClassHelper;

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
            if (! is_subclass_of($fqcn, $type)) {
                continue;
            }

            if (! method_exists($fqcn, $method)) {
                continue;
            }

            $phpcsFile->addError(
                sprintf('Method “%s” is forbidden, use “%s” instead.', $typeAndMethod, $replacement),
                $classPointer,
                self::FORBIDDEN_METHOD_DECLARATION
            );
        }
    }
}
