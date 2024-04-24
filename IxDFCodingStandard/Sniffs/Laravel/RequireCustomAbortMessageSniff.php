<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Laravel;

use IxDFCodingStandard\TokenHelper;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class RequireCustomAbortMessageSniff implements Sniff
{
    private const CODE_MISSING_PARAMETER = 'MissingRequiredAbortParameter';

    /** @var array<string, int> Where value is a number of required parameters */
    private array $functionsWithMinParameters = [
        'abort' => 2,
        'abort_if' => 3,
        'abort_unless' => 3,
    ];

    /** @var list<string> */
    private array $functionNames = [];

    /** @inheritDoc */
    public function register(): array
    {
        // cache to improve performance
        $this->functionNames = array_keys($this->functionsWithMinParameters);

        return [\T_STRING];
    }

    /** @param int $functionPointer */
    public function process(File $phpcsFile, $functionPointer): void // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, SlevomatCodingStandard.Functions.FunctionLength.FunctionLength, SlevomatCodingStandard.Files.FunctionLength.FunctionLength, SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    {
        $tokens = $phpcsFile->getTokens();

        $prevToken = $phpcsFile->findPrevious(\T_WHITESPACE, ($functionPointer - 1), null, true);
        if ($prevToken === false) {
            return;
        }

        // If function call is directly preceded by a NS_SEPARATOR it points to the
        // global namespace, so we should still catch it.
        if ($tokens[$prevToken]['code'] === \T_NS_SEPARATOR) {
            $prevToken = $phpcsFile->findPrevious(\T_WHITESPACE, ($prevToken - 1), null, true);
            if ($prevToken === false) {
                return;
            }

            if ($tokens[$prevToken]['code'] === \T_STRING) {
                // Not in the global namespace.
                return;
            }
        }

        $ignore = [
            \T_DOUBLE_COLON => true,
            \T_OBJECT_OPERATOR => true,
            \T_NULLSAFE_OBJECT_OPERATOR => true,
            \T_FUNCTION => true,
            \T_CONST => true,
            \T_PUBLIC => true,
            \T_PRIVATE => true,
            \T_PROTECTED => true,
            \T_AS => true,
            \T_NEW => true,
            \T_INSTEADOF => true,
            \T_NS_SEPARATOR => true,
            \T_IMPLEMENTS => true,
        ];

        if (isset($ignore[$tokens[$prevToken]['code']]) === true) {
            // Not a call to a PHP function.
            return;
        }

        $nextEffectiveTokenPosition = TokenHelper::findNextEffective($phpcsFile, $functionPointer + 1);

        if ($nextEffectiveTokenPosition === null) {
            // Not a call to a PHP function.
            return;
        }

        if ($tokens[$nextEffectiveTokenPosition]['code'] !== \T_OPEN_PARENTHESIS) {
            // Not a call to a PHP function.
            return;
        }

        $functionName = strtolower($tokens[$functionPointer]['content']);

        if (! \in_array($functionName, $this->functionNames, true)) {
            return;
        }

        $closeFunctionParenthesisTokenPosition = $tokens[$nextEffectiveTokenPosition]['parenthesis_closer'];
        $functionNestedParenthesisLevels = count($tokens[$functionPointer]['nested_parenthesis'] ?? []);
        $functionParameterDividerCommas = [];
        while ($nextEffectiveTokenPosition < $closeFunctionParenthesisTokenPosition) {
            $nextEffectiveTokenPosition = TokenHelper::findNextEffective(
                $phpcsFile,
                $nextEffectiveTokenPosition + 1,
                $closeFunctionParenthesisTokenPosition + 1
            );

            if ($nextEffectiveTokenPosition === null) {
                throw new \Exception('Parsing error: No effective tokens found ');
            }

            if ($functionName === 'abort'
                && $tokens[$nextEffectiveTokenPosition]['type'] === 'T_VARIABLE'
                && count($functionParameterDividerCommas) === 0
            ) {
                // Skip because we don't know what the variable type is: it can be Response or Responsable instance
                // @todo Implement it
                return;
            }

            if ($functionName === 'abort'
                && $tokens[$nextEffectiveTokenPosition]['type'] === 'T_OPEN_PARENTHESIS'
                && count($functionParameterDividerCommas) === 0
            ) {
                // Skip because it can be a function call that return Response or Responsable instance
                // @todo Implement it
                return;
            }

            if ($tokens[$nextEffectiveTokenPosition]['type'] !== 'T_COMMA') {
                continue;
            }

            $commaToken = $tokens[$nextEffectiveTokenPosition];
            if ((count($commaToken['nested_parenthesis']) - 1) === $functionNestedParenthesisLevels) {
                $functionParameterDividerCommas[] = $nextEffectiveTokenPosition;
            }
        }

        $numberOfUsedParameters = count($functionParameterDividerCommas) + 1;
        $minNumberOfExpectedParameters = $this->functionsWithMinParameters[$functionName];
        if ($numberOfUsedParameters < $minNumberOfExpectedParameters) {
            $phpcsFile->addError(
                sprintf('Missing required parameter for “%s” function: %d parameters provided, at least %d parameters expected', $functionName, $numberOfUsedParameters, $minNumberOfExpectedParameters),
                $functionPointer,
                self::CODE_MISSING_PARAMETER
            );
        }
    }
}
