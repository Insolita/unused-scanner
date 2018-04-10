<?php

use insolita\Scanner\Lib\Config;
use insolita\Scanner\Lib\Scanner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
class ScannerTest extends TestCase
{
    public function testDetection()
    {
        $config = new Config(__DIR__ . '/../composer.json', __DIR__ . '/../vendor', [__DIR__ . '/stubs/']);
        $map = [
            'insolita\Scanner\Lib\Config'=>1,
            'insolita\Scanner\Lib\ComposerReader'=>2,
            'Symfony\Component\Finder\Exception'=>3,
            'Symfony\Component\Finder\Finder'=>4,
            'Text_Template'=>5
        ];
        $scanner = new Scanner($map, $config, new Finder(), function (){}, function (){});
        $founds = $scanner->scan();
        sort($founds);
        print_r($founds);
        $this->assertEquals([1,2,3,4,5], $founds);
    }
}
