<?php declare(strict_types=1);

namespace IxDFCodingStandard\PhpCsFixer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Rules::class)]
#[CoversClass(Config::class)]
final class RulesTest extends TestCase
{
    #[Test]
    public function it_returns_a_non_empty_ruleset(): void
    {
        self::assertNotEmpty(Rules::get());
    }

    #[Test]
    public function it_builds_a_config_with_the_shared_rules(): void
    {
        $config = Config::create(__DIR__);

        self::assertSame(Rules::get(), $config->getRules());
        self::assertTrue($config->getRiskyAllowed());
    }
}
