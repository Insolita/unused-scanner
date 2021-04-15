<?php

namespace tests;

use insolita\Scanner\Lib\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public  function testConfigShouldSkipUnexistedDirs()
    {
        $params = require __DIR__ . '/scanner_test_config_dev.php';
        $config = Config::create($params);
        $dirs = $config->getScanDirectories();
        $this->assertContains(__DIR__ . '/stubs', $dirs);
        $this->assertNotContains(__DIR__ . '/not_existed', $dirs);
        $this->assertNotContains(__DIR__ . '/missing', $dirs);
    }
}