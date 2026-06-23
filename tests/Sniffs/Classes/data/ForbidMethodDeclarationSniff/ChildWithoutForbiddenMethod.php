<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Classes\data\ForbidMethodDeclarationSniff;

final class ChildWithoutForbiddenMethod extends ForbiddenParent
{
    public function send(): bool
    {
        return true;
    }
}
