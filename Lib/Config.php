<?php
declare(strict_types=1);

namespace insolita\Scanner\Lib;

use insolita\Scanner\Exceptions\InvalidConfigException;
use const DIRECTORY_SEPARATOR;

final class Config
{
    private $composerJsonPath;
    
    private $vendorPath;
    
    private $scanDirectories = [];
    
    private $excludeDirectories = [];
    
    private $scanFiles = [];
    
    private $requireDev = false;
    
    public function __construct(string $composerJsonPath, string $vendorPath, array $scanDirectories)
    {
        if (!mb_strpos($composerJsonPath, 'composer.json')) {
            $composerJsonPath = rtrim($composerJsonPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'composer.json';
        }
        $this->composerJsonPath = $composerJsonPath;
        $this->vendorPath = rtrim($vendorPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->scanDirectories = array_map(function ($path) {
            return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }, $scanDirectories);
    }
    
    public function getComposerJsonPath(): string
    {
        return $this->composerJsonPath;
    }
    
    public function getVendorPath(string $append = ''): string
    {
        return $this->vendorPath . $append;
    }
    
    public function getScanDirectories(): array
    {
        return $this->scanDirectories ?? [];
    }
    
    public function getRequireDev(): bool
    {
        return $this->requireDev;
    }
    
    public function setRequireDev(bool $requireDev): Config
    {
        $this->requireDev = $requireDev;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getScanFiles(): array
    {
        return $this->scanFiles ?? [];
    }
    
    /**
     * @param array $scanFiles
     *
     * @return Config
     */
    public function setScanFiles(array $scanFiles): Config
    {
        $this->scanFiles = $scanFiles;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getExcludeDirectories(): array
    {
        return $this->excludeDirectories ?? [];
    }
    
    /**
     * @param array $excludeDirectories
     *
     * @return Config
     */
    public function setExcludeDirectories(array $excludeDirectories): Config
    {
        $this->excludeDirectories = $excludeDirectories;
        return $this;
    }
    
    /**
     * @param array $data
     *
     * @return \insolita\Scanner\Lib\Config
     * @throws \insolita\Scanner\Exceptions\InvalidConfigException
     */
    public static function create(array $data): Config
    {
        if (!isset($data['composerJsonPath'], $data['vendorPath'], $data['scanDirectories'])) {
            throw new InvalidConfigException('missing required keys');
        }
        $config = new self($data['composerJsonPath'], $data['vendorPath'], $data['scanDirectories']);
        if (isset($data['requireDev'])) {
            $config->setRequireDev((bool)$data['requireDev']);
        }
        if (isset($data['scanFiles'])) {
            $config->setScanFiles((array)$data['scanFiles']);
        }
        if (isset($data['excludeDirectories'])) {
            $config->setExcludeDirectories($data['excludeDirectories']);
        }
        return $config;
    }
}
