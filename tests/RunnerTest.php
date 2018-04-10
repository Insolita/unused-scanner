<?php
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
    
    public function testInternal(){
        $runner = new Runner('/srv/http/newsublev/protected/scanner_config.php', false);
        $exitCode = $runner->run();
        $this->assertEquals(Runner::HAS_UNUSED_CODE, $exitCode);
    }
}
