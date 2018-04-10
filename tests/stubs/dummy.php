<?php
use insolita\Scanner\Lib\Config;

class Foo
{
    public function bar()
    {
        return [
            'a'=>Config::class,
            'b'=> insolita\Scanner\Lib\ComposerReader::class,
            'c'=> 'Symfony\Component\Finder\Exception\AccessDeniedException',
            'd'=> 'Symfony\\Component\\Finder\\Finder',
            'e'=> Text_Template::class
        ];
    }
}
