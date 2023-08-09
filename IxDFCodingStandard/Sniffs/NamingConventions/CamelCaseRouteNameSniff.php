<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class CamelCaseRouteNameSniff implements Sniff
{
    public const CODE_NOT_CAMEL_CASE_ROUTE_NAME = 'NotCamelCaseRouteName';

    private const PATTERN_CAMEL_CASE = '/^[^-_\s]++$/';

    /** @inheritDoc */
    public function register(): array
    {
        return [\T_STRING];
    }

    /** @inheritDoc */
    public function process(File $phpcsFile, $stringPointer): void
    {
        $tokens = $phpcsFile->getTokens();
        $currentToken = $tokens[$stringPointer];

        if ($currentToken['content'] !== 'name') {
            return;
        }

        $this->processRouteName($phpcsFile, $stringPointer, $tokens);
    }

    /**
     * Finds the route name and tests it against the camel case pattern.
     * If an issue is found, it adds an error.
     * @param array<string, int, array<string>> $tokens
     **/
    public function processRouteName(File $phpcsFile, int $stackPointer, array $tokens): void
    {
        $routeNameLocation = $phpcsFile->findNext(\T_CONSTANT_ENCAPSED_STRING, $stackPointer);
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
            if (preg_match(self::PATTERN_CAMEL_CASE, $part) !== 1) {
                return false;
            }
        }

        return true;
    }
}
