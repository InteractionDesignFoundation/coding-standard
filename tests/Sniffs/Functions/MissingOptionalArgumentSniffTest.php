<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Functions;

use IxDFCodingStandard\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(MissingOptionalArgumentSniff::class)]
final class MissingOptionalArgumentSniffTest extends TestCase
{
    #[Test]
    public function it_does_not_report_when_all_arguments_passed(): void
    {
        $report = self::checkFile(__DIR__.'/data/missingOptionalArgumentNoErrors.php', [
            'functions' => [
                'route' => 3,
            ],
        ]);

        self::assertNoSniffErrorInFile($report);
    }

    #[Test]
    public function it_reports_about_missing_function_argument(): void
    {
        $report = self::checkFile(__DIR__.'/data/missingOptionalArgumentErrors.php', [
            'functions' => [
                'route' => 3,
            ],
        ]);

        self::assertSame(2, $report->getErrorCount());
        self::assertSniffError($report, 3, MissingOptionalArgumentSniff::CODE_MISSING_OPTIONAL_ARGUMENT);
        self::assertSniffError($report, 4, MissingOptionalArgumentSniff::CODE_MISSING_OPTIONAL_ARGUMENT);
    }

    #[Test]
    public function it_reports_about_missing_method_argument(): void
    {
        $report = self::checkFile(__DIR__.'/data/missingOptionalArgumentErrors.php', [
            'staticMethods' => [
                'Some::route' => 3,
            ],
        ]);

        self::assertSame(1, $report->getErrorCount());
        self::assertSniffError($report, 6, MissingOptionalArgumentSniff::CODE_MISSING_OPTIONAL_ARGUMENT);
    }
}
