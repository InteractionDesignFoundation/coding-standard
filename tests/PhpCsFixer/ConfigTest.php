<?php declare(strict_types=1);

namespace IxDFCodingStandard\PhpCsFixer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(Config::class)]
final class ConfigTest extends TestCase
{
    private string $projectDir;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir().'/ixdf-cs-finder-'.bin2hex(random_bytes(4));

        $filesystem = new Filesystem();
        $filesystem->mkdir($this->projectDir.'/.git');
        $filesystem->dumpFile($this->projectDir.'/.gitignore', "/generated/\n");
        foreach (['app/Model.php', 'public/index.php', 'generated/Proxy.php', 'storage/framework/Cached.php', 'vendor/Lib.php'] as $file) {
            $filesystem->dumpFile($this->projectDir.'/'.$file, "<?php\n");
        }
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->projectDir);
    }

    #[Test]
    public function default_finder_skips_gitignored_and_excluded_files_but_keeps_public(): void
    {
        $found = [];
        foreach (Config::finder($this->projectDir) as $file) {
            $found[] = $file->getRelativePathname();
        }

        sort($found);

        self::assertSame(['app/Model.php', 'public/index.php'], $found);
    }
}
