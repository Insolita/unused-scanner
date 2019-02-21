<?php

namespace insolita\Scanner\Lib;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use function array_filter;
use function array_merge;
use function basename;
use function call_user_func;
use function in_array;
use function is_dir;
use function is_file;
use function is_null;
use function preg_match;
use function str_replace;

final class Scanner
{
    /**
     * @var array
     */
    private $searchPatterns;
    
    /**
     * @var \insolita\Scanner\Lib\Config
     */
    private $config;
    
    /**
     * @var \Symfony\Component\Finder\Finder
     */
    private $finder;
    
    private $usageFounds = [];
    
    private $usageReport = [];
    
    /**
     * @var callable
     */
    private $onNextDirectory;
    
    /**
     * @var callable
     */
    private $onDirectoryProgress;
    
    /**
     * @var bool
     */
    private $reportMode;
    
    public function __construct(
        array $searchPatterns,
        Config $config,
        Finder $finder,
        callable $onNextDirectory,
        callable $onDirectoryProgress
    ) {
        $this->searchPatterns = $searchPatterns;
        $this->config = $config;
        $this->finder = $finder;
        $this->onNextDirectory = $onNextDirectory;
        $this->onDirectoryProgress = $onDirectoryProgress;
        $this->reportMode = !is_null($config->getReportPath());
    }
    
    /**
     * @return array
     */
    public function scan()
    {
        foreach ($this->config->getScanDirectories() as $directory) {
            if (is_dir($directory)) {
                call_user_func($this->onNextDirectory, $directory);
                $this->scanDirectory($directory);
            }
            if (empty($this->searchPatterns)) {
                break;
            }
        }
        $this->scanAdditionalFiles();
        return array_unique($this->usageFounds);
    }
    
    /**
     * @return array
     */
    public function getUsageReport()
    {
        return $this->usageReport;
    }
    
    private function scanAdditionalFiles()
    {
        if (!empty($this->searchPatterns) && !empty($this->config->getScanFiles())) {
            call_user_func($this->onNextDirectory, ' additional files');
            $total = count($this->config->getScanFiles());
            foreach ($this->config->getScanFiles() as $iteration => $filename) {
                if (is_file($filename)) {
                    $file = new SplFileInfo($filename, $filename, basename($filename));
                    $this->reportMode === false
                        ? $this->checkUsage($file)
                        : $this->collectUsage($file);
                }
                if (empty($this->searchPatterns)) {
                    call_user_func($this->onDirectoryProgress, $total, $total, $filename);
                    break;
                } else {
                    call_user_func($this->onDirectoryProgress, $iteration + 1, $total, $filename);
                }
            }
        }
    }
    
    private function scanDirectory($directory)
    {
        $finder = clone $this->finder;
        $files = $finder->files()->in([$directory])->exclude($this->config->getExcludeDirectories());
        foreach ($this->config->getExtensions() as $extension) {
            $files->name($extension);
        }
        $total = $files->count();
        $iteration = 0;
        foreach ($files as $file) {
            /**@var SplFileInfo $file * */
            $this->reportMode === false
                ? $this->checkUsage($file)
                : $this->collectUsage($file);
            if (empty($this->searchPatterns)) {
                call_user_func($this->onDirectoryProgress, $total, $total, $file->getRealPath());
                break;
            } else {
                $iteration++;
                call_user_func($this->onDirectoryProgress, $iteration, $total, $file->getRealPath());
            }
        }
    }
    
    private function checkUsage(SplFileInfo $file)
    {
        $usageFounds = [];
        $fileContent = $file->getContents();
        foreach ($this->searchPatterns as $definition => $packageName) {
            if (in_array($packageName, $usageFounds)) {
                continue;
            }
            $preparedDefinition = str_replace('\\', '\\\\', $definition);
            $pattern = "/[\s\t\n\.\,<=>\'\"\[\(;\\\\]{$preparedDefinition}/";
            $isMatched = !is_null($this->config->getCustomMatch())
                ? call_user_func($this->config->getCustomMatch(), $definition, $packageName, $file)
                : false;
            if (!$isMatched) {
                $isMatched = preg_match($pattern, str_replace('\\\\', '\\', $fileContent));
            }
            if ($isMatched) {
                $usageFounds[] = $packageName;
            }
        }
        $this->registerFounds($usageFounds);
    }
    
    private function collectUsage(SplFileInfo $file)
    {
        $usageFounds = [];
        $fileContent = $file->getContents();
        foreach ($this->searchPatterns as $definition => $packageName) {
            $preparedDefinition = str_replace('\\', '\\\\', $definition);
            $pattern = "/[\s\t\n\.\,<=>\'\"\[\(;\\\\]{$preparedDefinition}/";
            $isMatched = !is_null($this->config->getCustomMatch())
                ? call_user_func($this->config->getCustomMatch(), $definition, $packageName, $file)
                : false;
            if (!$isMatched) {
                $isMatched = preg_match($pattern, str_replace('\\\\', '\\', $fileContent));
            }
            if ($isMatched) {
                $usageFounds[] = $packageName;
                if($this->reportMode === true){
                    $this->collectFounds($packageName, $definition, $file->getRealPath());
                }
            }
        }
        $this->registerFounds($usageFounds);
    }
    
    /**
     * @param string $packageName
     * @param string $definition
     * @param string $fileName
     */
    private function collectFounds($packageName,  $definition,  $fileName)
    {
        if (!isset($this->usageReport[$packageName])) {
            $this->usageReport[$packageName] = [];
        }
        if (!isset($this->usageReport[$packageName][$definition])) {
            $this->usageReport[$packageName][$definition] = [];
        }
        $this->usageReport[$packageName][$definition][] = $fileName;
    }
    
    private function registerFounds(array $usageFounds)
    {
        $this->usageFounds = array_merge($this->usageFounds, $usageFounds);
        if ($this->reportMode !== true) {
            $this->searchPatterns = array_filter($this->searchPatterns, function ($packageName) use (&$usageFounds) {
                return !in_array($packageName, $usageFounds);
            });
        }
    }
}
