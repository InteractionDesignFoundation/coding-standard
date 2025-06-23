<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\NamingConventions;

use IxDFCodingStandard\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CamelCaseRouteNameSniff::class)]
final class CamelCaseRouteNameSniffTest extends TestCase
{
    #[Test]
    public function it_does_not_report_about_camel_case_route_name(): void
    {
        $report = self::checkFile(__DIR__.'/data/routes/routeNameUsesCamelCase.php');

        self::assertNoSniffErrorInFile($report);
    }

    #[Test]
    public function it_reports_about_kebab_case_route_name(): void
    {
        $report = self::checkFile(__DIR__.'/data/routes/routeNameUsesKebabCase.php');

        self::assertSame(1, $report->getErrorCount());
        self::assertSniffError($report, 3, CamelCaseRouteNameSniff::CODE_NOT_CAMEL_CASE_ROUTE_NAME);
    }

    #[Test]
    public function it_reports_about_snake_case_route_name(): void
    {
        $report = self::checkFile(__DIR__.'/data/routes/routeNameUsesKebabCase.php');

        self::assertSame(1, $report->getErrorCount());
        self::assertSniffError($report, 3, CamelCaseRouteNameSniff::CODE_NOT_CAMEL_CASE_ROUTE_NAME);
    }
}
