<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Classes;

use IxDFCodingStandard\Sniffs\Classes\data\ForbidMethodDeclarationSniff\ForbiddenParent;
use IxDFCodingStandard\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ForbidMethodDeclarationSniff::class)]
final class ForbidMethodDeclarationSniffTest extends TestCase
{
    private const array FORBIDDEN_METHODS = [
        ForbiddenParent::class.'::shouldSend' => '\App\Notifications\Support\ShouldCheckConditionsBeforeSendingOut::shouldBeSent',
    ];

    #[Test]
    #[RequiresPhp('>= 8.5.0')]
    public function it_reports_forbidden_method_even_when_the_class_uses_a_deprecated_trait(): void
    {
        // #[\Deprecated] is only allowed on traits since PHP 8.5 (earlier versions reject the fixture at compile time).
        // Resolving the class autoloads it; using a deprecated trait then emits a deprecation, and the sniff must
        // silence it instead of letting it abort the whole file check (Internal.Exception).
        $deprecations = [];
        set_error_handler(
            static function (int $errno, string $message) use (&$deprecations): bool {
                $deprecations[] = $message;

                return true;
            },
            \E_DEPRECATED | \E_USER_DEPRECATED
        );

        try {
            $report = self::checkFile(
                __DIR__.'/data/ForbidMethodDeclarationSniff/ChildDeclaringForbiddenMethod.php',
                ['forbiddenMethods' => self::FORBIDDEN_METHODS]
            );
        } finally {
            restore_error_handler();
        }

        self::assertSame([], $deprecations, 'The sniff must not leak deprecations triggered while autoloading the analysed class.');
        self::assertSame(1, $report->getErrorCount());
        self::assertSniffError($report, 5, ForbidMethodDeclarationSniff::FORBIDDEN_METHOD_DECLARATION);
    }

    #[Test]
    public function it_does_not_report_when_the_subclass_does_not_declare_the_forbidden_method(): void
    {
        $report = self::checkFile(
            __DIR__.'/data/ForbidMethodDeclarationSniff/ChildWithoutForbiddenMethod.php',
            ['forbiddenMethods' => self::FORBIDDEN_METHODS]
        );

        self::assertNoSniffErrorInFile($report);
    }
}
