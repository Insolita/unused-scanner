<?php
declare(strict_types=1);

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
    private $scanDirectories;
    private $excludeDirectories = [];
    
    private $skipPackages = [];
    
    private $scanFiles = [];
    private $requireDev = false;
    private $extensions = ['*.php'];
    private $customMatch;
    private $reportPath;
    private $reportExtension = '.json';
    private $reportFormatter;
    
    public function __construct(string $composerJsonPath, string $vendorPath, array $scanDirectories)
    {
        if (!mb_strpos($composerJsonPath, 'composer.json')) {
            $composerJsonPath = rtrim($composerJsonPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'composer.json';
        }
        $this->composerJsonPath = $composerJsonPath;
        $this->vendorPath = realpath(rtrim($vendorPath, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR;
        $this->scanDirectories = array_filter(array_map(static function ($path) {
            return realpath(rtrim($path, DIRECTORY_SEPARATOR). DIRECTORY_SEPARATOR);
        }, $scanDirectories));
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
     * @return array
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }
    
    /**
     * @param array $extensions
     *
     * @return Config
     */
    public function setExtensions(array $extensions): Config
    {
        $this->extensions = $extensions;
        return $this;
    }
    
    /**
     * @return null|callable
     */
    public function getCustomMatch():?callable
    {
        return $this->customMatch;
    }
    
    /**
     * @param callable $customMatch
     *
     * @return Config
     */
    public function setCustomMatch(callable $customMatch):Config
    {
        $this->customMatch = $customMatch;
        return $this;
    }
    
    /**
     * @param string $reportPath
     *
     * @return Config
     */
    public function setReportPath(string $reportPath):Config
    {
        $this->reportPath = $reportPath;
        return $this;
    }
    
    public function getReportPath():?string
    {
        return $this->reportPath;
    }
    
    /**
     * @param callable $reportFormatter
     *
     * @return Config
     */
    public function setReportFormatter(callable $reportFormatter):Config
    {
        $this->reportFormatter = $reportFormatter;
        return $this;
    }
    
    public function getReportFormatter():?callable
    {
        return $this->reportFormatter;
    }
    
    /**
     * @param string $reportExtension
     */
    public function setReportExtension(string $reportExtension): void
    {
        $this->reportExtension = $reportExtension;
    }
    
    /**
     * @return string
     */
    public function getReportExtension(): string
    {
        return $this->reportExtension;
    }
    
    /**
     * @return array
     */
    public function getSkipPackages(): array
    {
        return $this->skipPackages;
    }
    
    /**
     * @param array $skipPackages
     */
    public function setSkipPackages(array $skipPackages): void
    {
        $this->skipPackages = $skipPackages;
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
