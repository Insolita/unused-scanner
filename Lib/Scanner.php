<?php
declare(strict_types=1);

namespace insolita\Scanner\Lib;

use function basename;
use function is_null;
use Symfony\Component\Finder\Finder;
use function array_filter;
use function array_merge;
use function call_user_func;
use function file_get_contents;
use function in_array;
use function is_dir;
use function is_file;
use function preg_match;
use function preg_match_all;
use function str_replace;
use Symfony\Component\Finder\SplFileInfo;

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
    
    /**
     * @var callable
     */
    private $onNextDirectory;
    
    /**
     * @var callable
     */
    private $onDirectoryProgress;
    
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
    
    private function scanAdditionalFiles()
    {
        if (!empty($this->searchPatterns) && !empty($this->config->getScanFiles())) {
            call_user_func($this->onNextDirectory, ' additional files');
            $total = count($this->config->getScanFiles());
            foreach ($this->config->getScanFiles() as $iteration => $filename) {
                if (is_file($filename)) {
                    $file = new SplFileInfo($filename, $filename, basename($filename));
                    $this->checkUsage($file);
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
    
    private function scanDirectory(string $directory)
    {
        $finder = clone $this->finder;
        $files = $finder->files()->in([$directory])->exclude($this->config->getExcludeDirectories());
        foreach ($this->config->getExtensions() as $extension) {
            $files->name($extension);
        }
        $total = $files->count();
        $iteration = 0;
        foreach ($files as $file) {
            /**@var SplFileInfo $file**/
            $this->checkUsage($file);
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
                ?call_user_func($this->config->getCustomMatch(), $definition, $packageName, $file)
                :false;
            if(!$isMatched){
                $isMatched = preg_match($pattern, str_replace('\\\\', '\\',$fileContent));
            }
            if($isMatched){
                $usageFounds[] = $packageName;
            }
        }
        $this->registerFounds($usageFounds);
    }
    
    private function registerFounds(array $usageFounds)
    {
        $this->usageFounds = array_merge($this->usageFounds, $usageFounds);
        $this->searchPatterns = array_filter($this->searchPatterns, function ($packageName) use (&$usageFounds) {
            return !in_array($packageName, $usageFounds);
        });
    }
}
