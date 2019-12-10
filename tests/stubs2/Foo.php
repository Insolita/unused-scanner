<?php
use TheSeer\Tokenizer\{NamespaceUri, NamespaceUriException};
use insolita\Scanner\Lib\Config;
use Symfony\Thanks\{
    GitHubClient as HubClient,
    Thanks
};
use PhpCsFixer\Console\SelfUpdate\GithubClient;

use Symfony\Component\Console\{
    Helper\Table as XXX,
    Input\ArrayInput,
    Input\InputInterface as YYY,
    Output\NullOutput,
    Output\OutputInterface,
    Question\Question,
    Question\ChoiceQuestion as Choice,
    Question\ConfirmationQuestion
};

class Foo{

    public function bar($a)
    {
         if(empty($a)){
             throw new NamespaceUriException('dummy');
         }
         return new NamespaceUri($a);
    }

    public function baz(){
        $client = new GithubClient();
        $thanks = new Thanks();
    }
}