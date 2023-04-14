<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Functions;

use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\ForbiddenFunctionsSniff as BaseForbiddenFunctionsSniff;

final class ForbiddenFunctionsSniff extends BaseForbiddenFunctionsSniff
{
    /** @inheritDoc */
    public $forbiddenFunctions = [];
}
