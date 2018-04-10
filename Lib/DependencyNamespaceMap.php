<?php
declare(strict_types=1);

namespace insolita\Scanner\Lib;

use function array_reduce;
use function file_exists;
use function mb_strpos;

final class DependencyNamespaceMap
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
                if (mb_strpos(implode(',', $pathMap), $pathPart) !== false) {
                    $this->addToMap($definition, $packageName);
                    break;
                }
            }
        }
    
        foreach ($this->loadPsr() as $definition => $pathMap) {
            foreach ($this->dependencies as $packageName => $pathPart) {
                if (mb_strpos(implode(',', $pathMap), $pathPart) !== false) {
                    $this->addToMap($definition, $packageName);
                    break;
                }
            }
        }
    
        foreach ($this->loadClassmap() as $definition => $path) {
            foreach ($this->dependencies as $packageName => $pathPart) {
                if (mb_strpos($path, $pathPart) !== false) {
                    $this->addToMap($definition, $packageName);
                    break;
                }
            }
        }
        return $this->map;
    }
    
    public function prepareDependencies(array $dependencies)
    {
        return array_reduce($dependencies, function ($carry, $name) {
            $carry[$name] = realpath($this->config->getVendorPath() . $name);
            return $carry;
        }, []);
    }

    
    private function addToMap($definition, $packageName)
    {
        $this->map[$definition] = $packageName;
    }
    
    private function loadNamespaces(): array
    {
        if (file_exists($this->config->getVendorPath('composer/autoload_namespaces.php'))) {
            return require_once $this->config->getVendorPath('composer/autoload_namespaces.php');
        } else {
            return [];
        }
    }
    
    private function loadPsr(): array
    {
        if (file_exists($this->config->getVendorPath('composer/autoload_psr4.php'))) {
            return require_once $this->config->getVendorPath('composer/autoload_psr4.php');
        } else {
            return [];
        }
    }
    
    private function loadClassmap(): array
    {
        if (file_exists($this->config->getVendorPath('composer/autoload_classmap.php'))) {
            return require_once $this->config->getVendorPath('composer/autoload_classmap.php');
        } else {
            return [];
        }
    }
}
