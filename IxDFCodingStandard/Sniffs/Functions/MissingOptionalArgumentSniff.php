<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Functions;

use IxDFCodingStandard\Helpers\TokenHelper;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/** Inspired by {@see \SlevomatCodingStandard\Sniffs\Functions\StrictCallSniff}. */
final class MissingOptionalArgumentSniff implements Sniff
{
    public const CODE_MISSING_OPTIONAL_ARGUMENT = 'MissingOptionalArgument';

    /** @var array<string, int> */
    public array $functions = [];

    /** @var array<string, int> */
    public array $staticMethods = [];

    /** @return array<int, (int|string)> */
    public function register(): array
    {
        return TokenHelper::NAME_TOKEN_CODES;
    }

    /** @inheritDoc */
    public function process(File $phpcsFile, $stringPointer): void // phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    {
        $tokens = $phpcsFile->getTokens();

        $parenthesisOpenerPointer = TokenHelper::findNextEffective($phpcsFile, $stringPointer + 1);
        if (! is_int($parenthesisOpenerPointer) || $tokens[$parenthesisOpenerPointer]['code'] !== \T_OPEN_PARENTHESIS) {
            return;
        }

        $parenthesisCloserPointer = $tokens[$parenthesisOpenerPointer]['parenthesis_closer'];
        assert(is_int($parenthesisCloserPointer));

        $functionName = strtolower(ltrim($tokens[$stringPointer]['content'], '\\'));

        $previousPointer = TokenHelper::findPreviousEffective($phpcsFile, $stringPointer - 1);

        if (in_array($tokens[$previousPointer]['code'], [...Tokens::$methodPrefixes, \T_FUNCTION], true)) {
            return; // skip function/methods declarations
        }

        $isMethodCall = in_array($tokens[$previousPointer]['code'], [\T_OBJECT_OPERATOR, \T_DOUBLE_COLON], true);
        $fullyQualifiedFunctionName = $functionName;

        if ($isMethodCall) {
            $fqcn = $this->getClassNameOfMethodCall($phpcsFile, $stringPointer);
            $fullyQualifiedFunctionName = "$fqcn::$functionName";

            if (! array_key_exists($fullyQualifiedFunctionName, $this->staticMethods)) {
                return;
            }

            $expectedArgumentsNumber = $this->staticMethods[$fullyQualifiedFunctionName];
        } elseif (array_key_exists($functionName, $this->functions)) {
            $expectedArgumentsNumber = $this->functions[$functionName];
        } else {
            return;
        }

        $actualArgumentsNumber = $this->countArguments($phpcsFile, ['opener' => $parenthesisOpenerPointer, 'closer' => $parenthesisCloserPointer]);

        if ($actualArgumentsNumber < $expectedArgumentsNumber) {
            $phpcsFile->addError(
                sprintf('Missing argument in %s() call: %d arguments used, at least %d expected.', $fullyQualifiedFunctionName, $actualArgumentsNumber, $expectedArgumentsNumber),
                $stringPointer,
                self::CODE_MISSING_OPTIONAL_ARGUMENT
            );
        }
    }

    /** @param array{opener: int, closer: int} $parenthesisPointers */
    private function countArguments(File $phpcsFile, array $parenthesisPointers): int // phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    {
        $tokens = $phpcsFile->getTokens();

        $commaPointers = [];
        for ($i = $parenthesisPointers['opener'] + 1; $i < $parenthesisPointers['closer']; $i++) {
            if ($tokens[$i]['code'] === \T_OPEN_PARENTHESIS) {
                $i = $tokens[$i]['parenthesis_closer'];
                continue;
            }

            if ($tokens[$i]['code'] === \T_OPEN_SHORT_ARRAY) {
                $i = $tokens[$i]['bracket_closer'];
                continue;
            }

            if ($tokens[$i]['code'] === \T_COMMA) {
                $commaPointers[] = $i;
            }
        }

        $commaPointersCount = count($commaPointers);

        $actualArgumentsNumber = $commaPointersCount + 1;
        $lastCommaPointer = $commaPointersCount > 0 ? $commaPointers[$commaPointersCount - 1] : null;

        if (
            $lastCommaPointer !== null
            && TokenHelper::findNextEffective($phpcsFile, $lastCommaPointer + 1, $parenthesisPointers['closer']) === null
        ) {
            $actualArgumentsNumber--;
        }

        return $actualArgumentsNumber;
    }

    /**
     * Given a position of a method call token, find the class name it belongs to.
     * @param int $stackPointer The position of the token in the stack passed in $tokens.
     * @return class-string|null Returns class name if found, null otherwise.
     */
    private function getClassNameOfMethodCall(File $phpcsFile, int $stackPointer): ?string // phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    {
        $tokens = $phpcsFile->getTokens();

        // Go back and find the object operator or double colon
        $operator = $phpcsFile->findPrevious(
            [\T_OBJECT_OPERATOR, \T_DOUBLE_COLON],
            $stackPointer - 1
        );

        if ($operator === false) {
            return null; // It's not a method call on an object or static class method call
        }

        // For static calls using ::
        if ($tokens[$operator]['code'] === \T_DOUBLE_COLON) {
            // Get the string before the double colon, which should be the class name or self, parent, etc.
            $prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, $operator - 1, null, true);
            if (
                $tokens[$prev]['code'] === \T_STRING
                || $tokens[$prev]['code'] === \T_SELF
                || $tokens[$prev]['code'] === \T_PARENT
                || $tokens[$prev]['code'] === \T_STATIC
            ) {
                return $tokens[$prev]['content'];
            }
        }

        // For object instance calls using ->
        if ($tokens[$operator]['code'] === \T_OBJECT_OPERATOR) {
            // Finding the variable or the string before -> which could be the object instance
            $prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, $operator - 1, null, true);
            if ($tokens[$prev]['code'] === \T_VARIABLE) {
                // Classname presented as a variable, getting actual class name for an instance variable
                // is complex and may require more in-depth analysis or static code analysis tools.
                return null;
            }
        }

        return null;
    }
}
