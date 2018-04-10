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
            'insolita\Scanner\Lib\DependencyNamespaceMap'=>0,
            'insolita\Scanner\Lib\Config'=>1,
            'insolita\Scanner\Lib\ComposerReader'=>2,
            'Symfony\Component\Finder\Exception'=>3,
            'Symfony\Component\Finder\Finder'=>4,
            'Text_Template'=>5,
            'PHPUnit\\Runner\\'=>6,
            'PHP_Token_AMPERSAND'=>7,
            'Exception'=>8,
            'Composer\\Autoload\\'=>9,
            'SebastianBergmann\\'=>10,
            'PHPUnit\\Util\\'=>11,
            'DeepCopy\\Filter\\'=>12,
            'phpDocumentor\\Reflection\\'=>13,
            'Webmozart\\Assert\\Tests\\'=>14,
            'Webmozart\Assert\Assert'=>15,
            'Prophecy\Exception'=>16
        ];
        $scanner = new Scanner($map, $config, new Finder(), function (){}, function (){});
        $founds = $scanner->scan();
        sort($founds);
        $this->assertEquals([0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16], $founds);
    }
}
