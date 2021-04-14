<?php
namespace tests;

use insolita\Scanner\Lib\Config;
use insolita\Scanner\Lib\Scanner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ScannerTest extends TestCase
{
    protected static $map
        = [
            'insolita\Scanner\Lib\DependencyMapper' => 0,
            'insolita\Scanner\Lib\Config' => 1,
            'insolita\Scanner\Lib\ComposerReader' => 2,
            'Symfony\Component\Finder\Exception' => 3,
            'Symfony\Component\Finder\Finder' => 4,
            'Text_Template' => 5,
            'PHPUnit\\Runner\\' => 6,
            'PHP_Token_AMPERSAND' => 7,
            'Exception' => 8,
            'Composer\\Autoload\\' => 9,
            'SebastianBergmann\\' => 10,
            'PHPUnit\\Util\\' => 11,
            'DeepCopy\\Filter\\' => 12,
            'phpDocumentor\\Reflection\\' => 13,
            'Webmozart\\Assert\\Tests\\' => 14,
            'Webmozart\Assert\Assert' => 15,
            'Prophecy\Exception' => 16,
            'A2I\\GeoBundle\\' => 17,
            'Bazinga\\GeocoderBundle\\' => 18,
        ];

    public function testDetection()
    {
        $config = new Config(__DIR__ . '/../composer.json', __DIR__ . '/../vendor', [__DIR__ . '/stubs/']);
        $scanner = new Scanner(self::$map, $config, new Finder(), function () {
        }, function () {
        });
        $founds = $scanner->scan();
        sort($founds);
        $this->assertEquals([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16], $founds);
    }

    public function testScanAdditionalFiles()
    {
        $config = new Config(__DIR__ . '/../composer.json', __DIR__ . '/../vendor', []);
        $config->setScanFiles([__DIR__ . '/stubs/dummy.php']);
        $scanner = new Scanner(self::$map, $config, new Finder(), function () {
        }, function () {
        });
        $founds = $scanner->scan();
        sort($founds);
        $this->assertEquals([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16], $founds);
    }

    public function testScanWithCustomMatch()
    {
        $map = array_merge(self::$map, ['Foo\Bar' => 17, 'Bar\Baz' => 18]);
        $config = new Config(__DIR__ . '/../composer.json', __DIR__ . '/../vendor', [__DIR__ . '/stubs/']);
        $config->setCustomMatch(function ($definition, $packageName, SplFileInfo $file) {
            if ($packageName === 18) {
                return true;
            }

            if ($file->getExtension() === 'twig') {
                $definition = str_replace('\\', '/', $definition);
                if (mb_strpos($file->getContents(), $definition) !== false) {
                    return true;
                }
            }
            return false;
        })->setExtensions(['*.php', '*.twig']);
        $scanner = new Scanner($map, $config, new Finder(), function () {
        }, function () {
        });
        $founds = $scanner->scan();
        sort($founds);
        $this->assertEquals([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18], $founds);
    }

    public function testYmlScan()
    {
        $config = new Config(__DIR__ . '/../composer.json', __DIR__ . '/../vendor', [__DIR__ . '/stubs/']);
        $config->setExtensions(['*.yml']);
        $fn = function () {
        };
        $scanner = new Scanner(self::$map, $config, new Finder(), $fn, $fn);
        $founds = $scanner->scan();
        sort($founds);
        $this->assertEquals([17], $founds);
    }

    public function testSymfonyCustomScan()
    {
        $config = (new Config(__DIR__ . '/../composer.json', __DIR__ . '/../vendor', [__DIR__ . '/stubs/']))
            ->setExtensions(['*.yml'])
            ->setCustomMatch(function ($definition, $packageName, SplFileInfo $file) {
                if ($file->getExtension() === 'yml') {
                    $bundleDefinition = '@'.mb_strtolower(
                        preg_replace('/\\\\/', '_', str_replace('Bundle', '', $definition),1)
                    );
                    $bundleDefinition = rtrim($bundleDefinition, '\\');
                    if (mb_strpos($file->getContents(), $bundleDefinition) !== false) {
                        return true;
                    }
                }
            });
        $fn = function () {
        };
        $scanner = new Scanner(self::$map, $config, new Finder(), $fn, $fn);
        $founds = $scanner->scan();
        sort($founds);
        $this->assertEquals([17, 18], $founds);
    }

    public function testScanGroupedNamespaces()
    {
        $config = new Config(__DIR__ . '/../composer.json', __DIR__ . '/../vendor', [__DIR__ . '/stubs2/']);
        $patterns = [
            'Symfony\Thanks\GitHubClient' => 0,
            'Symfony\Thanks\Thanks' => 1,
            'TheSeer\Tokenizer\NamespaceUri' => 2,
            'TheSeer\Tokenizer\NamespaceUriException' => 3,
            'Symfony\Component\Console\Input\ArrayInput' => 4,
            'TheSeer\Tokenizer\GitHubClient' => 9, //Should be not matched
        ];
        $scanner = new Scanner($patterns, $config, new Finder(), function () {}, function () {});
        $founds = $scanner->scan();
        sort($founds);
        $this->assertEquals([0, 1, 2, 3, 4], $founds);
    }
}
