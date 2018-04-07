<?php
declare(strict_types=1);

namespace insolita\Scanner\Lib;

final class ComposerReader
{
    /**
     * @var Config
     */
    private $config;
    
    public function __construct(Config $config)
    {
        $this->config = $config;
    }
    
    public function fetchDependencies(): array
    {
        $composerData = $this->readComposerJson();
        $packages = $composerData['require'];
        if($this->config->getRequireDev()===true){
            $packages = array_merge($packages, $composerData['require-dev'] ?? []);
        }
        return array_keys($packages);
    }
    
    private function readComposerJson(): array
    {
        $path = $this->config->getComposerJsonPath();
        if (!mb_strpos($path, 'composer.json')) {
            $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'composer.json';
        }
        $file = file_get_contents($path);
        
        return $file ? json_decode($file, true) : [];
    }
}
