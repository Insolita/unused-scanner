<?php
declare(strict_types=1);

namespace insolita\Scanner\Lib;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use function array_filter;
use function array_merge;
use function array_slice;
use function basename;
use function call_user_func;
use function count;
use function explode;
use function implode;
use function in_array;
use function is_dir;
use function is_file;
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
        $this->reportMode = $config->getReportPath() !== null;
    }
    
    public function scan(): array
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
    public function getUsageReport(): array
    {
        return $this->usageReport;
    }
    
    private function scanAdditionalFiles():void
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
                }

                call_user_func($this->onDirectoryProgress, $iteration + 1, $total, $filename);
            }
        }
    }
    
    private function scanDirectory(string $directory):void
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
            }

            $iteration++;
            call_user_func($this->onDirectoryProgress, $iteration, $total, $file->getRealPath());
        }
    }
    
    private function checkUsage(SplFileInfo $file):void
    {
        $usageFounds = [];
        $fileContent = $file->getContents();
        foreach ($this->searchPatterns as $definition => $packageName) {
            if (in_array($packageName, $usageFounds, true)) {
                continue;
            }
            $isMatched = $this->matchDefinition($definition, $packageName, $fileContent, $file);
            if ($isMatched) {
                $usageFounds[] = $packageName;
            }
        }
        $this->registerFounds($usageFounds);
    }
    
    private function collectUsage(SplFileInfo $file):void
    {
        $usageFounds = [];
        $fileContent = $file->getContents();
        foreach ($this->searchPatterns as $definition => $packageName) {
            $isMatched = $this->matchDefinition($definition, $packageName, $fileContent, $file);
            if ($isMatched) {
                $usageFounds[] = $packageName;
                if($this->reportMode === true){
                    $this->collectFounds($packageName, $definition, $file->getRealPath());
                }
            }
        }
        $this->registerFounds($usageFounds);
    }
    
    private function collectFounds(string $packageName, string $definition, string $fileName):void
    {
        if (!isset($this->usageReport[$packageName])) {
            $this->usageReport[$packageName] = [];
        }
        if (!isset($this->usageReport[$packageName][$definition])) {
            $this->usageReport[$packageName][$definition] = [];
        }
        $this->usageReport[$packageName][$definition][] = $fileName;
    }
    
    private function registerFounds(array $usageFounds):void
    {
        $this->usageFounds = array_merge($this->usageFounds, $usageFounds);
        if ($this->reportMode !== true) {
            $this->searchPatterns = array_filter($this->searchPatterns, function ($packageName) use (&$usageFounds) {
                return !in_array($packageName, $usageFounds, true);
            });
        }
    }

    /**
     * @param                                       $definition
     * @param                                       $packageName
     * @param string                                $fileContent
     * @param \Symfony\Component\Finder\SplFileInfo $file
     * @return bool|false|int|mixed
     */
    private function matchDefinition($definition, $packageName, string $fileContent, SplFileInfo $file)
    {
        $preparedDefinition = str_replace('\\', '\\\\', $definition);
        $pattern = "/[\s\t\n\.\,<=>\'\"\[\(;\\\\]{$preparedDefinition}/";
        $isMatched = $this->config->getCustomMatch() !== null
            ? call_user_func($this->config->getCustomMatch(), $definition, $packageName, $file)
            : false;
        $content =  str_replace('\\\\', '\\', $fileContent);
        if (!$isMatched) {
            $isMatched = preg_match($pattern, $content);
        }
        if(!$isMatched){
            $parts = array_filter(explode('\\', str_replace('\\\\', '\\', $definition)));
            $partsCount = count($parts);
            if($partsCount > 1 && strpos($content, $parts[0].'\\') !== false){
                $i=1;
                while ($i < $partsCount && !$isMatched){
                    $head = implode('\\\\', array_slice($parts, 0, $partsCount - $i));
                    $tail = implode('\\\\', array_slice($parts, $partsCount - $i));
                    $pattern = "~{$head}\\\{[^\{]*{$tail}[^\{]*\}~";
                    $isMatched = preg_match($pattern, $content);
                    $i++;
                }
            }
        }
        return $isMatched;
    }
}
