<?php

namespace insolita\Scanner\Lib;

use function array_filter;
use function in_array;
use function mb_strpos;

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
    
    /**
     * @return array
     */
    public function fetchDependencies()
    {
        $composerData = $this->readComposerJson();
        $packages = $composerData['require'];
        if ($this->config->getRequireDev()===true) {
            $packages = array_merge($packages, $composerData['require-dev'] ?? []);
        }
        $packages = array_keys($packages);
        return array_filter($packages, function ($package) {
            $packageHasVendor = mb_strpos($package, '/') !== false;
            $packageNotSkipped = !in_array($package, $this->config->getSkipPackages());
            return $packageHasVendor && $packageNotSkipped;
        });
    }
    
    /**
     * @return array
     */
    private function readComposerJson()
    {
        $file = file_get_contents($this->config->getComposerJsonPath());
        return $file ? json_decode($file, true) : [];
    }
}
