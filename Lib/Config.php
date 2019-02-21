<?php

namespace insolita\Scanner\Lib;

use insolita\Scanner\Exceptions\InvalidConfigException;
use function is_array;
use function is_callable;
use function is_dir;
use function is_string;
use function rtrim;
use const DIRECTORY_SEPARATOR;

final class Config
{
    private $composerJsonPath;
    private $vendorPath;
    private $scanDirectories = [];
    private $excludeDirectories = [];
    
    private $skipPackages = [];
    
    private $scanFiles = [];
    private $requireDev = false;
    private $extensions = ['*.php'];
    private $customMatch = null;
    private $reportPath = null;
    private $reportExtension = '.json';
    private $reportFormatter = null;
    
    public function __construct($composerJsonPath,  $vendorPath, array $scanDirectories)
    {
        if (!mb_strpos($composerJsonPath, 'composer.json')) {
            $composerJsonPath = rtrim($composerJsonPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'composer.json';
        }
        $this->composerJsonPath = $composerJsonPath;
        $this->vendorPath = realpath(rtrim($vendorPath, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR;
        $this->scanDirectories = array_map(function ($path) {
            return realpath(rtrim($path, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR;
        }, $scanDirectories);
    }
    
    /**
     * @return string
     */
    public function getComposerJsonPath()
    {
        return $this->composerJsonPath;
    }
    
    /**
     * @param string $append
     *
     * @return string
     */
    public function getVendorPath($append = '')
    {
        return $this->vendorPath . $append;
    }
    
    /**
     * @return array
     */
    public function getScanDirectories()
    {
        return empty($this->scanDirectories) ? []: $this->scanDirectories;
    }
    
    /**
     * @return bool
     */
    public function getRequireDev()
    {
        return $this->requireDev;
    }
    
    /**
     * @param $requireDev
     *
     * @return $this
     */
    public function setRequireDev($requireDev)
    {
        $this->requireDev = $requireDev;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getScanFiles()
    {
        return empty($this->scanFiles)? [] : $this->scanFiles;
    }
    
    /**
     * @param array $scanFiles
     *
     * @return Config
     */
    public function setScanFiles(array $scanFiles)
    {
        $this->scanFiles = $scanFiles;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getExcludeDirectories()
    {
        return empty($this->excludeDirectories)? []: $this->excludeDirectories;
    }
    
    /**
     * @param array $excludeDirectories
     *
     * @return Config
     */
    public function setExcludeDirectories(array $excludeDirectories)
    {
        $this->excludeDirectories = $excludeDirectories;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
    
    /**
     * @param array $extensions
     *
     * @return Config
     */
    public function setExtensions(array $extensions)
    {
        $this->extensions = $extensions;
        return $this;
    }
    
    /**
     * @return null|callable
     */
    public function getCustomMatch()
    {
        return $this->customMatch;
    }
    
    /**
     * @param callable $customMatch
     *
     * @return Config
     */
    public function setCustomMatch(callable $customMatch)
    {
        $this->customMatch = $customMatch;
        return $this;
    }
    
    /**
     * @param string $reportPath
     *
     * @return Config
     */
    public function setReportPath($reportPath)
    {
        $this->reportPath = $reportPath;
        return $this;
    }
    
    /**
     * @return null|string
     */
    public function getReportPath()
    {
        return $this->reportPath;
    }
    
    /**
     * @param callable $reportFormatter
     *
     * @return Config
     */
    public function setReportFormatter($reportFormatter)
    {
        $this->reportFormatter = $reportFormatter;
        return $this;
    }
    
    /**
     * @return null|callable
     */
    public function getReportFormatter()
    {
        return $this->reportFormatter;
    }
    
    /**
     * @param string $reportExtension
     */
    public function setReportExtension($reportExtension)
    {
        $this->reportExtension = $reportExtension;
    }
    
    /**
     * @return string
     */
    public function getReportExtension()
    {
        return $this->reportExtension;
    }
    
    /**
     * @return array
     */
    public function getSkipPackages()
    {
        return $this->skipPackages;
    }
    
    /**
     * @param array $skipPackages
     */
    public function setSkipPackages(array $skipPackages)
    {
        $this->skipPackages = $skipPackages;
    }
    
    /**
     * @param array $data
     *
     * @return \insolita\Scanner\Lib\Config
     * @throws \insolita\Scanner\Exceptions\InvalidConfigException
     */
    public static function create(array $data)
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
        if (isset($data['extensions']) && !empty($data['extensions'])) {
            $config->setExtensions($data['extensions']);
        }
        if (isset($data['customMatch']) && is_callable($data['customMatch'])) {
            $config->setCustomMatch($data['customMatch']);
        }
        if (isset($data['reportPath']) && is_dir($data['reportPath'])) {
            $path = rtrim($data['reportPath'], DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            $config->setReportPath($path);
        }
        if (isset($data['reportFormatter']) && is_callable($data['reportFormatter'])) {
            $config->setReportFormatter($data['reportFormatter']);
        }
        if (isset($data['reportExtension']) && is_string($data['reportExtension'])) {
            $config->setReportExtension('.'.ltrim($data['reportExtension'], '.'));
        }
        if (isset($data['skipPackages']) && is_array($data['skipPackages'])) {
            $config->setSkipPackages($data['skipPackages']);
        }
        return $config;
    }
}
