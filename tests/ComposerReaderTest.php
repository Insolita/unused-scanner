<?php
namespace tests;

use insolita\Scanner\Lib\ComposerReader;
use insolita\Scanner\Lib\Config;
use PHPUnit\Framework\TestCase;

class ComposerReaderTest extends TestCase
{
    public function testItShouldBeSkipPhpExtPackages()
    {
        $config = new Config(__DIR__ . '/stub_composer.json', __DIR__ . '/../vendor', [__DIR__ . '/stubs/']);
        $config->setRequireDev(true);
        $reader = new ComposerReader($config);
        $packages = $reader->fetchDependencies();
        $this->assertNotContains('php', $packages);
        $this->assertNotContains('ext-gd', $packages);
        $this->assertContains('symfony/finder', $packages);
        $this->assertContains('phpunit/phpunit', $packages);
        $this->assertContains('symfony/thanks', $packages);
    }
    
    public function testItShouldBeSkipCustomConfiguredPackages()
    {
        $config = new Config(__DIR__ . '/stub_composer.json', __DIR__ . '/../vendor', [__DIR__ . '/stubs/']);
        $config->setRequireDev(true);
        $config->setSkipPackages(['symfony/finder', 'ext-gd', 'phpunit/phpunit']);
        $reader = new ComposerReader($config);
        $packages = $reader->fetchDependencies();
        $this->assertNotContains('php', $packages);
        $this->assertNotContains('ext-gd', $packages);
        $this->assertNotContains('symfony/finder', $packages);
        $this->assertNotContains('phpunit/phpunit', $packages);
        $this->assertContains('symfony/thanks', $packages);
    }
}
