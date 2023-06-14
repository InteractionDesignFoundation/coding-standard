<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;

/**
 * Checks the meaningful naming of variables and member variables.
 * Created based on \PHP_CodeSniffer\Standards\Zend\Sniffs\NamingConventions\ValidVariableNameSniff
 */
final class MeaningfulVariableNameSniff extends AbstractVariableSniff
{
    /** @var array<string, string> */
    public array $forbiddenNames = [];

    /**
     * Processes this test, when one of its tokens is encountered.
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int|string $stackPtr The position of the current token in the stack passed in $tokens.
     */
    protected function processVariable(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $varName = ltrim($tokens[$stackPtr]['content'], '$');

        if ($this->checkForProhibitedVariableNames($varName)) {
            $error = 'Variable "%s" has not very meaningful, searchable or pronounceable name';
            $phpcsFile->addError($error, $stackPtr, 'NotMeaningfulVariableName', [$varName]);
            return;
        }
    }

    /**
     * Processes class member variables.
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int|string $stackPtr The position of the current token in the stack passed in $tokens.
     */
    protected function processMemberVar(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $varName = ltrim($tokens[$stackPtr]['content'], '$');

        if ($this->checkForProhibitedVariableNames($varName)) {
            $error = 'Variable "%s" has not very meaningful, searchable or pronounceable name';
            $phpcsFile->addError($error, $stackPtr, 'NotMeaningfulVariableName', [$varName]);
            return;
        }
    }

    /**
     * Processes the variable found within a double-quoted string.
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int|string $stackPtr The position of the double-quoted string.
     */
    protected function processVariableInString(File $phpcsFile, $stackPtr): void // phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    {
        $tokens = $phpcsFile->getTokens();

        $subject = $tokens[$stackPtr]['content'];

        if (preg_match_all('|[^\\\]\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)|', $subject, $matches) !== 0) {
            foreach ($matches[1] as $varName) {
                // If it’s a php reserved var, then it's ok.
                if (isset($this->phpReservedVars[$varName]) === true) {
                    continue;
                }

                if ($this->checkForProhibitedVariableNames($varName)) {
                    $error = 'Variable "%s" has not very meaningful or searchable name';
                    $phpcsFile->addError($error, $stackPtr, 'NotMeaningfulVariableName', [$varName]);
                    return;
                }
            }
        }
    }

    private function checkForProhibitedVariableNames(string $varName): bool
    {
        return isset($this->forbiddenNames[$varName]) === true;
    }
}
