<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Classes\data\ForbidMethodDeclarationSniff;

final class ChildDeclaringForbiddenMethod extends ForbiddenParent
{
    use DeprecatedTrait;

    public function shouldSend(): bool
    {
        return true;
    }
}
