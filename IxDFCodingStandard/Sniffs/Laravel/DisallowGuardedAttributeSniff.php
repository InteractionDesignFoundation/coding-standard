<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Laravel;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;
use SlevomatCodingStandard\Helpers\ClassHelper;

final class DisallowGuardedAttributeSniff extends AbstractScopeSniff
{
    public const CODE_EMPTY_GUARDED = 'EmptyGuarded';
    public const CODE_NON_EMPTY_GUARDED = 'NonEmptyGuarded';

    /**
     * A list of tokenizers this sniff supports.
     * @var list<string>
     */
    public array $supportedTokenizers = ['PHP'];

    /** Constructs the test with the tokens it wishes to listen for. */
    public function __construct()
    {
        parent::__construct([\T_CLASS], [\T_VARIABLE], false);
    }

    /** @inheritDoc */
    protected function processTokenWithinScope(File $phpcsFile, $varPointer, $currScope)
    {
        $varToken = $phpcsFile->getTokens()[$varPointer];

        if ($varToken['content'] !== '$guarded') {
            return;
        }

        $classTokenPointer = $phpcsFile->findPrevious([\T_CLASS], $varPointer);

        $classFQCN = ClassHelper::getFullyQualifiedName($phpcsFile, $classTokenPointer);

        $probablyModelInstance = new $classFQCN(); // @todo find a more performant option
        if (! $probablyModelInstance instanceof \Illuminate\Database\Eloquent\Model) {
            return;
        }

        $modelInstance = $probablyModelInstance;

        if ($modelInstance->getGuarded() === []) {
            $error = 'Usage of unguarded Model attributes is forbidden for security reasons.';
            $phpcsFile->addError($error, $varPointer, self::CODE_EMPTY_GUARDED);
            return;
        }

        if ($modelInstance->getGuarded() !== ['*']) {
            $error = 'Usage of unguarded Model attributes is forbidden for security reasons.';
            $phpcsFile->addError($error, $varPointer, self::CODE_NON_EMPTY_GUARDED);
            return;
        }
    }

    /** @inheritDoc */
    protected function processTokenOutsideScope(File $phpcsFile, $stackPtr)
    {
        // nothing to do here
    }
}
