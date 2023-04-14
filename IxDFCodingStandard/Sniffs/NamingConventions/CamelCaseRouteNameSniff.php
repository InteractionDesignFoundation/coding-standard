<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class CamelCaseRouteNameSniff implements Sniff
{
    private const CAMEL_CASE_PATTERN = '/^[^-_\s]++$/';

    public const CODE_NOT_CAMEL_CASE_ROUTE_NAME = 'NotCamelCaseRouteName';

    /** @inheritDoc */
    public function register(): array
    {
        return [\T_STRING];
    }

    /** @inheritDoc */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $currentToken = $tokens[$stackPtr];

        if ($currentToken['content'] !== 'name') {
            return;
        }

        $this->processRouteName($phpcsFile, $stackPtr, $tokens);
    }

    /**
     * Finds the route name and tests it against the camel case pattern. If an issue is found,
     * it adds an error.
     * @param array<string, int, array<string>> $tokens
     **/
    public function processRouteName(File $phpcsFile, int $stackPtr, array $tokens): void
    {
        $routeNameLocation = $phpcsFile->findNext(\T_CONSTANT_ENCAPSED_STRING, $stackPtr);
        $routeName = $tokens[$routeNameLocation]['content'];

        if (!$this->isCamelCase($routeName)) {
            $phpcsFile->addError(
                'Route name is not camel cased',
                $routeNameLocation,
                self::CODE_NOT_CAMEL_CASE_ROUTE_NAME
            );
        }
    }

    /**
     * Checks each part of the route name to determine whether
     * it matches the camel case pattern or not
     */
    private function isCamelCase(string $routeName): bool
    {
        $parts = explode('.', $routeName);

        foreach ($parts as $part) {
            if (preg_match(self::CAMEL_CASE_PATTERN, $part) !== 1) {
                return false;
            }
        }

        return true;
    }
}
