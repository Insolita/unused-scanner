<?php
use Carbon\Carbon;
use insolita\Scanner\Lib\Runner;
use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase
{
    public function testItShouldBeReturnSuccessExitCode()
    {
        $exitCode = (new Runner(__DIR__ . '/scanner_test_config_nodev.php', false))->run();
        $this->assertEquals(Runner::SUCCESS_CODE, $exitCode);
    }
    
    public function testItShouldBeReturnUnusedExitCode()
    {
        $exitCode = (new Runner(__DIR__ . '/scanner_test_config_dev.php', false))->run();
        $this->assertEquals(Runner::HAS_UNUSED_CODE, $exitCode);
    }
    
    public function testItShouldBeStoreJsonReport()
    {
        Carbon::setTestNow('2018-01-02 03:04:05');
        $reportFile = __DIR__ . '/reports/package_usage_report_2018-01-02_03_04.json';
        if(file_exists($reportFile)){
            unlink($reportFile);
        }
        $exitCode = (new Runner(__DIR__ . '/scanner_test_config_reported.php', false))->run();
        $this->assertEquals(Runner::SUCCESS_CODE, $exitCode);
        $this->assertFileExists($reportFile);
        $fileData = json_decode(file_get_contents($reportFile), true);
        print_r($fileData);
        $this->assertNotEmpty($fileData);
    }
    public function testItShouldBeStoreCustomFormattedReport()
    {
        Carbon::setTestNow('2018-01-02 03:04:05');
        $reportFile = __DIR__ . '/reports/package_usage_report_2018-01-02_03_04.txt';
        if(file_exists($reportFile)){
            unlink($reportFile);
        }
        $exitCode = (new Runner(__DIR__ . '/scanner_test_config_reported_custom.php', false))->run();
        $this->assertEquals(Runner::SUCCESS_CODE, $exitCode);
        $this->assertFileExists($reportFile);
        $fileData = file_get_contents($reportFile);
        print_r($fileData);
        $this->assertNotEmpty($fileData);
    }
}
