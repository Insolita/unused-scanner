<?php
declare(strict_types=1);

namespace insolita\Scanner\Lib;

use function array_reduce;
use function file_exists;
use function mb_strpos;

final class DependencyMapper
{
    /**
     * @var array
     */
    private $dependencies;
    
    /**
     * @var \insolita\Scanner\Lib\Config
     */
    private $config;
    
    private $map = [];
    
    public function __construct(Config $config, array $dependencies)
    {
        $this->config = $config;
        $this->dependencies = $this->prepareDependencies($dependencies);
    }
    
    public function build(): array
    {
        foreach ($this->loadNamespaces() as $definition => $pathMap) {
            foreach ($this->dependencies as $packageName => $pathPart) {
                if ($this->isPathMatched(implode(',', $pathMap), $pathPart)) {
                    $this->addToMap($definition, $packageName);
                    break;
                }
            }
        }
        
        foreach ($this->loadPsr() as $definition => $pathMap) {
            foreach ($this->dependencies as $packageName => $pathPart) {
                if ($this->isPathMatched(implode(',', $pathMap), $pathPart)) {
                    $this->addToMap($definition, $packageName);
                    break;
                }
            }
        }
        
        foreach ($this->loadClassmap() as $definition => $path) {
            foreach ($this->dependencies as $packageName => $pathPart) {
                if ($this->isPathMatched($path, $pathPart)) {
                    $this->addToMap($definition, $packageName);
                    break;
                }
            }
        }
        return $this->map;
    }
    
    public function prepareDependencies(array $dependencies)
    {
        return array_reduce($dependencies,
            function ($carry, $name) {
                $carry[$name] = $this->config->getVendorPath($name);
                return $carry;
            },
            []);
    }
    
    private function addToMap($definition, $packageName):void
    {
        $this->map[$definition] = $packageName;
    }
    
    private function loadNamespaces(): array
    {
        if (file_exists($this->config->getVendorPath('composer' . DIRECTORY_SEPARATOR . 'autoload_namespaces.php'))) {
            return require_once $this->config->getVendorPath('composer' . DIRECTORY_SEPARATOR. 'autoload_namespaces.php');
        }
        return [];
    }
    
    private function loadPsr(): array
    {
        if (file_exists($this->config->getVendorPath('composer' . DIRECTORY_SEPARATOR . 'autoload_psr4.php'))) {
            return require_once $this->config->getVendorPath('composer' . DIRECTORY_SEPARATOR . 'autoload_psr4.php');
        }
        return [];
    }
    
    private function loadClassmap(): array
    {
        if (file_exists($this->config->getVendorPath('composer' . DIRECTORY_SEPARATOR . 'autoload_classmap.php'))) {
            return require_once $this->config->getVendorPath('composer' . DIRECTORY_SEPARATOR . 'autoload_classmap.php');
        }
        return [];
    }
    
    private function isPathMatched(string $path, string $pathPart): bool
    {
        return mb_strpos($this->normalizePath($path), $this->normalizePath($pathPart)) !== false;
    }
    
    private function normalizePath(string $path): string
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = rtrim(strtr($path, '/\\', $ds . $ds), $ds);
        if (strpos($ds . $path, "{$ds}.") === false && strpos($path, "{$ds}{$ds}") === false) {
            return $path;
        }
        if (strpos($path, "{$ds}{$ds}") === 0 && $ds === '\\') {
            $parts = [$ds];
        } else {
            $parts = [];
        }
        foreach (explode($ds, $path) as $part) {
            if ($part === '..' && !empty($parts) && end($parts) !== '..') {
                array_pop($parts);
            } elseif ($part === '.' || ($part === '' && !empty($parts))) {
                continue;
            } else {
                $parts[] = $part;
            }
        }
        $path = implode($ds, $parts);
        return $path === '' ? '.' : $path;
    }
    
}
