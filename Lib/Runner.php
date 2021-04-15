<?php
declare(strict_types=1);

namespace insolita\Scanner\Lib;

use insolita\Scanner\Exceptions\InvalidConfigException;
use Symfony\Component\Finder\Finder;
use Throwable;
use function call_user_func;
use function file_put_contents;
use function json_encode;
use function sprintf;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

final class Runner
{
    public const SUCCESS_CODE = 0;
    public const GENERAL_ERROR_CODE = 1;
    public const ARGUMENT_ERROR_CODE = 2;
    public const CONFIG_ERROR_CODE = 4;
    public const SCANNING_ERROR_CODE = 8;
    public const HAS_UNUSED_CODE = 16;
    
    /**
     * @var string
     */
    private $configFile;
    
    /**
     * @var bool
     */
    private $silentMode;
    
    public function __construct(string $configFile, bool $silentMode)
    {
        $this->configFile = $configFile;
        $this->silentMode = $silentMode;
    }

    public function run():int
    {
        try {
            $config = $this->makeConfig();
            $this->output(' - config prepared' . PHP_EOL);
            $map = $this->makeDependencyMap($config);
            $this->output(' - search patterns prepared' . PHP_EOL);
        } catch (Throwable $e) {
            echo 'Error! ' . $e->getMessage() . PHP_EOL;
            echo $e->getTraceAsString() . PHP_EOL;
            return $e instanceof InvalidConfigException ? self::CONFIG_ERROR_CODE : self::GENERAL_ERROR_CODE;
        }
        try {
            $scanner = (new Scanner($map, $config, new Finder(), [$this, 'onNextDirectory'], [$this, 'onProgress']));
            $scanResult = $scanner->scan();
        } catch (Throwable $e) {
            echo 'Error! ' . $e->getMessage() . PHP_EOL;
            echo $e->getTraceAsString() . PHP_EOL;
            return self::SCANNING_ERROR_CODE;
        }
        if ($config->getReportPath() !== null) {
            $this->storeReport($scanner->getUsageReport(), $config);
        }
        return $this->showScanReport($map, $scanResult);
    }
    
    public function onNextDirectory(string $directory):void
    {
        $this->output(PHP_EOL . ' - Scan ' . $directory . PHP_EOL);
    }
    
    public function onProgress(int $done, int $total)
    {
        $width = 60;
        $percentage = round(($done * 100) / ($total <= 0 ? 1 : $total));
        $bar = (int)round(($width * $percentage) / 100);
        $this->output(sprintf("%s%%[%s>%s]\r", $percentage, str_repeat("=", $bar), str_repeat(" ", $width - $bar)));
    }
    
    private function makeConfig(): Config
    {
        $params = require $this->configFile;
        return Config::create($params);
    }
    
    private function makeDependencyMap(Config $config): array
    {
        $dependencies = (new ComposerReader($config))->fetchDependencies();
        return (new DependencyMapper($config, $dependencies))->build();
    }
    
    private function output(string $message):void
    {
        if ($this->silentMode === false) {
            echo $message;
        }
    }
    
    private function showScanReport(array $map, array $scanResult): int
    {
        $result = array_values(array_diff(array_unique(array_values($map)), $scanResult));
        if (empty($result)) {
            $this->output(PHP_EOL . 'No unused dependencies found!' . PHP_EOL);
            return self::SUCCESS_CODE;
        }

        $this->output(PHP_EOL . 'Unused dependencies found!' . PHP_EOL);
        array_walk($result,
            function($packageName) {
                $this->output(' -' . $packageName . PHP_EOL);
            });
        return self::HAS_UNUSED_CODE;
    }
    
    private function storeReport(array $usageReport, Config $config):void
    {
        $formattedReport = $config->getReportFormatter() !== null
            ? (string)call_user_func($config->getReportFormatter(), $usageReport)
            : json_encode($usageReport, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        $reportFileName = sprintf(
            '%spackage_usage_report_%s%s',
            $config->getReportPath(),
            date('Y-m-d_H_i'),
            $config->getReportExtension()
        );
        file_put_contents($reportFileName, $formattedReport);
    }
}
