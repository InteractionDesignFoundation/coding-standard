<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\NamingConventions;

use IxDFCodingStandard\TestCase;

/** @covers \IxDFCodingStandard\Sniffs\NamingConventions\CamelCaseRouteNameSniff */
final class CamelCaseRouteNameSniffTest extends TestCase
{
    /** @test */
    public function it_does_not_report_about_camel_case_route_name(): void
    {
        $report = self::checkFile(__DIR__.'/data/routeUsesCamelCase.php');

        self::assertNoSniffErrorInFile($report);
    }

    /** @test */
    public function it_reports_about_kebab_case_route_name(): void
    {
        $report = self::checkFile(__DIR__.'/data/routeUsesKebabCase.php', [], [], [__DIR__.'/data/routeUsesKebabCase.php']);

        self::assertSniffError($report, 3, CamelCaseRouteNameSniff::CODE_NOT_CAMEL_CASE_ROUTE_NAME);
    }

    /** @test */
    public function it_reports_about_snake_case_route_name(): void
    {
        $report = self::checkFile(__DIR__.'/data/routeUsesSnakeCase.php');

        self::assertSniffError($report, 3, CamelCaseRouteNameSniff::CODE_NOT_CAMEL_CASE_ROUTE_NAME);
    }
}
