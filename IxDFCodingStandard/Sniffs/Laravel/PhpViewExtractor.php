<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Laravel;

use BadMethodCallException;

final class PhpViewExtractor
{
    private const INVALID_METHOD_CALL = 'Invalid method call';

    /** @param array<array<string>> $tokens */
    public function isViewFunction(array $tokens, int $position): bool
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
    public function getViewFunctionTemplateName(array $tokens, int $position): string
    {
        if (!$this->isViewFunction($tokens, $position)) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        return mb_trim($tokens[$position + 2]['content'], '\'"');
    }
}
