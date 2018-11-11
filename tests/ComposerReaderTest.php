<?php

namespace insolita\Scanner\tests;

use insolita\Scanner\Lib\ComposerReader;
use insolita\Scanner\Lib\Config;
use PHPUnit\Framework\TestCase;
use function print_r;

/**
 * @author insolita
 * Created [11.11.18 20:51]
 */
class ComposerReaderTest extends TestCase
{
    public function testItShouldBeSkipPhpExtPackages()
    {
        $config = new Config(__DIR__ . '/stub_composer.json', __DIR__ . '/../vendor', [__DIR__ . '/stubs/']);
        $config->setRequireDev(true);
        $reader = new ComposerReader($config);
        $packages = $reader->fetchDependencies();
        print_r($packages);
        $this->assertNotContains('php', $packages);
        $this->assertNotContains('ext-gd', $packages);
        $this->assertContains('symfony/finder', $packages);
        $this->assertContains('phpunit/phpunit', $packages);
        $this->assertContains('symfony/thanks', $packages);
    }
}
