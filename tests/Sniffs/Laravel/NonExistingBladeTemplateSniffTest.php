<?php declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Laravel;

use IxDFCodingStandard\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(NonExistingBladeTemplateSniff::class)]
final class NonExistingBladeTemplateSniffTest extends TestCase
{
    #[Test]
    public function it_detects_missing_blade_include_template(): void
    {
        $report = self::checkFile(__DIR__.'/data/NonExistingBladeTemplateSniff/blade_includes.blade.php', [
            'baseDir' => __DIR__.'/data/NonExistingBladeTemplateSniff',
        ]);

        self::assertSame(2, $report->getErrorCount());
        self::assertSniffError($report, 2, NonExistingBladeTemplateSniff::CODE_TEMPLATE_NOT_FOUND);
        self::assertSniffError($report, 3, NonExistingBladeTemplateSniff::CODE_TEMPLATE_NOT_FOUND);
    }

    #[Test]
    public function it_detects_missing_blade_conditional_include_template(): void
    {
        $report = self::checkFile(__DIR__.'/data/NonExistingBladeTemplateSniff/conditional_includes.blade.php', [
            'baseDir' => __DIR__.'/data/NonExistingBladeTemplateSniff',
        ]);

        self::assertSame(2, $report->getErrorCount());
        self::assertSniffError($report, 2, NonExistingBladeTemplateSniff::CODE_TEMPLATE_NOT_FOUND);
        self::assertSniffError($report, 3, NonExistingBladeTemplateSniff::CODE_TEMPLATE_NOT_FOUND);
    }

    #[Test]
    public function it_detects_missing_view_function_template(): void
    {
        $report = self::checkFile(__DIR__.'/data/NonExistingBladeTemplateSniff/view_function.php', [
            'baseDir' => __DIR__.'/data/NonExistingBladeTemplateSniff',
        ]);

        self::assertSame(2, $report->getErrorCount());
        self::assertSniffError($report, 5, NonExistingBladeTemplateSniff::CODE_TEMPLATE_NOT_FOUND);
        self::assertSniffError($report, 6, NonExistingBladeTemplateSniff::CODE_TEMPLATE_NOT_FOUND);
    }

    #[Test]
    public function it_detects_unknown_view_namespace(): void
    {
        $report = self::checkFile(__DIR__.'/data/NonExistingBladeTemplateSniff/namespaced_templates.blade.php', [
            'baseDir' => __DIR__.'/data/NonExistingBladeTemplateSniff',
            'viewNamespaces' => [
                'known' => 'resources/views/vendor/known',
            ],
        ]);

        self::assertSame(1, $report->getErrorCount());
        self::assertSniffWarning($report, 2, NonExistingBladeTemplateSniff::CODE_UNKNOWN_VIEW_NAMESPACE);
        self::assertSniffError($report, 4, NonExistingBladeTemplateSniff::CODE_TEMPLATE_NOT_FOUND);
    }

    #[Test]
    public function it_ignores_dynamic_template_names(): void
    {
        $report = self::checkFile(__DIR__.'/data/NonExistingBladeTemplateSniff/dynamic_templates.blade.php', [
            'baseDir' => __DIR__.'/data/NonExistingBladeTemplateSniff',
        ]);

        self::assertNoSniffErrorInFile($report);
    }

    #[Test]
    public function it_ignores_templates_ending_with_special_chars(): void
    {
        $report = self::checkFile(__DIR__.'/data/NonExistingBladeTemplateSniff/special_endings.blade.php', [
            'baseDir' => __DIR__.'/data/NonExistingBladeTemplateSniff',
        ]);

        self::assertNoSniffErrorInFile($report);
    }

    #[Test]
    public function it_finds_existing_templates(): void
    {
        $report = self::checkFile(__DIR__.'/data/NonExistingBladeTemplateSniff/existing_templates.blade.php', [
            'baseDir' => __DIR__.'/data/NonExistingBladeTemplateSniff',
            'viewNamespaces' => [
                'known' => 'resources/views/vendor/known',
            ],
        ]);

        self::assertNoSniffErrorInFile($report);
    }

    #[Test]
    public function it_detects_missing_templates_in_extended_directives(): void
    {
        $report = self::checkFile(__DIR__.'/data/NonExistingBladeTemplateSniff/extended_directives.blade.php', [
            'baseDir' => __DIR__.'/data/NonExistingBladeTemplateSniff',
        ]);

        self::assertSame(3, $report->getErrorCount());
        self::assertSniffError($report, 1, NonExistingBladeTemplateSniff::CODE_TEMPLATE_NOT_FOUND); // admin.missing1 (first in array)
        self::assertSniffError($report, 2, NonExistingBladeTemplateSniff::CODE_TEMPLATE_NOT_FOUND); // components.missing1 (first in array)
        self::assertSniffError($report, 3, NonExistingBladeTemplateSniff::CODE_TEMPLATE_NOT_FOUND); // admin.missing
    }
}
