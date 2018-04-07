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
        $file = file_get_contents($this->config->getComposerJsonPath());
        return $file ? json_decode($file, true) : [];
    }
}
