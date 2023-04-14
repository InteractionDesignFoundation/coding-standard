<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Files;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/** Checks that all file names are BEM-cased. */
final class BemCasedFilenameSniff implements Sniff
{
    private const ERROR_TOO_MANY_DELIMITERS = 'TooManyElementModifiers';
    private const ERROR_TOO_MANY_MODIFIERS = 'TooManyElementModifiers';
    private const ERROR_INVALID_CHARACTERS = 'InvalidCharacters';

    private const MODIFIER_DELIMITER = '--';
    private const ELEMENT_DELIMITER = '__';
    private const BEM_FILE_NAME_PATTERN = '/^(?:(?:__)?[a-z][a-zA-Z0-9]*(?:--[\w][a-zA-Z0-9]*)?)+?\.blade\.php$/';
    private const BLADE_COMPONENT_FILE_NAME_PATTERN = '/^([a-z]+(-[a-z]+)+)\.blade\.php$/';

    /** @var array<string, bool> */
    private array $checkedFiles = [];

    /** @inheritDoc */
    public function register(): array
    {
        return [\T_INLINE_HTML, \T_OPEN_TAG];
    }

    /** @inheritDoc */
    public function process(File $phpcsFile, $stackPtr): int // phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    {
        $filename = $phpcsFile->getFilename();
        if (! str_ends_with($filename, '.blade.php')) {
            return 0;
        }

        $hash = md5($filename);
        if (isset($this->checkedFiles[$hash]) || $filename === 'STDIN') {
            return 0;
        }

        $this->checkedFiles[$hash] = true;

        $filename = basename($filename);

        if ($this->hasInvalidNumberOfElements($filename)) {
            $phpcsFile->addError(
                'Filename “%s” has too many element delimiters',
                $stackPtr,
                self::ERROR_TOO_MANY_DELIMITERS,
                [$filename]
            );
            $phpcsFile->recordMetric($stackPtr, 'BEM element delimiters', 'no');
        } else {
            $phpcsFile->recordMetric($stackPtr, 'BEM element delimiters', 'yes');
        }

        if (substr_count($filename, self::MODIFIER_DELIMITER) > 1) {
            $phpcsFile->addError(
                'Filename “%s” has to many element modifiers',
                $stackPtr,
                self::ERROR_TOO_MANY_MODIFIERS,
                [$filename]
            );
            $phpcsFile->recordMetric($stackPtr, 'BEM element modifiers', 'no');
        } else {
            $phpcsFile->recordMetric($stackPtr, 'BEM element modifiers', 'yes');
        }

        if ($this->hasCorrectFileName($filename)) {
            $phpcsFile->recordMetric($stackPtr, 'BEM case filename', 'yes');
        } else {
            $error = 'Filename “%s” does not match the expected BEM filename convention';
            $phpcsFile->addError($error, $stackPtr, self::ERROR_INVALID_CHARACTERS, [$filename]);
            $phpcsFile->recordMetric($stackPtr, 'BEM case filename', 'no');
        }

        // Ignore the rest of the file.
        return $phpcsFile->numTokens + 1;
    }

    private function hasInvalidNumberOfElements(string $filename): bool
    {
        return count(explode(self::ELEMENT_DELIMITER, ltrim($filename, self::ELEMENT_DELIMITER))) > 3;
    }

    private function hasCorrectFileName(string $filename): bool
    {
        return $this->isBemFilename($filename)
            || $this->isComponentFileName($filename)
            || $this->isErrorPage($filename);
    }

    private function isBemFilename(string $filename): bool
    {
        return preg_match(self::BEM_FILE_NAME_PATTERN, $filename) === 1;
    }

    private function isComponentFileName(string $filename): bool
    {
        return preg_match(self::BLADE_COMPONENT_FILE_NAME_PATTERN, $filename) === 1;
    }

    private function isErrorPage(string $filename): bool
    {
        return preg_match('/^([45])\d{2}\.blade\.php$/', $filename) === 1;
    }
}
