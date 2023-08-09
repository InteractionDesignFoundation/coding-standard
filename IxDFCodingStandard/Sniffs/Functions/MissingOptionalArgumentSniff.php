<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\TokenHelper;

/** Inspired by {@see \SlevomatCodingStandard\Sniffs\Functions\StrictCallSniff}. */
final class MissingOptionalArgumentSniff implements Sniff
{
    public const CODE_MISSING_OPTIONAL_ARGUMENT = 'MissingOptionalArgument';

    /** @var array<string, int> */
    public array $functions = [];

    /** @return array<int, (int|string)> */
    public function register(): array
    {
        return TokenHelper::getOnlyNameTokenCodes();
    }

    /** @inheritDoc */
    public function process(File $phpcsFile, $stringPointer): void
    {
        $tokens = $phpcsFile->getTokens();

        $parenthesisOpenerPointer = TokenHelper::findNextEffective($phpcsFile, $stringPointer + 1);
        if (! is_int($parenthesisOpenerPointer) || $tokens[$parenthesisOpenerPointer]['code'] !== \T_OPEN_PARENTHESIS) {
            return;
        }

        $parenthesisCloserPointer = $tokens[$parenthesisOpenerPointer]['parenthesis_closer'];
        assert(is_int($parenthesisCloserPointer));

        $functionName = strtolower(ltrim($tokens[$stringPointer]['content'], '\\'));

        if (! array_key_exists($functionName, $this->functions)) {
            return;
        }

        $previousPointer = TokenHelper::findPreviousEffective($phpcsFile, $stringPointer - 1);
        if (in_array($tokens[$previousPointer]['code'], [\T_OBJECT_OPERATOR, \T_DOUBLE_COLON, \T_FUNCTION], true)) {
            return;
        }

        $actualArgumentsNumber = $this->countArguments($phpcsFile, ['opener' => $parenthesisOpenerPointer, 'closer' => $parenthesisCloserPointer]);
        $expectedArgumentsNumber = $this->functions[$functionName];

        if ($actualArgumentsNumber < $expectedArgumentsNumber) {
            $phpcsFile->addError(
                sprintf('Missing argument in %s() call: %d arguments used, at least %d expected.', $functionName, $actualArgumentsNumber, $expectedArgumentsNumber),
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
}
