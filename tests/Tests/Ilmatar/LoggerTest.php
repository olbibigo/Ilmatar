<?php
namespace Tests\Ilmatar;

use Ilmatar\Tests\AbstractTestCase;
use Carbon\Carbon;

class LoggerTest extends AbstractTestCase
{
    /**
     * @group LoggerTest
     * @group LoggerTest::testLoggerExist
     */
    public function testLoggerExist()
    {
        $this->assertTrue(isset($this->app["monolog.channels"]["value"]));
        foreach ($this->app["monolog.channels"]["value"] as $channel) {
            $this->assertTrue(isset($this->app['monolog.' . $channel]));
        }
    }
    /**
     * @group LoggerTest
     * @group LoggerTest::testReplaceTag
     */
    public function testLogger()
    {
        $filepath = dirname($this->app['monolog.logfile']) . '/test.mailer.log';

        $string = Carbon::now()->toISO8601String();
     
        $this->app['monolog.mailer']->info("INFO" . $string);
        $this->app['monolog.mailer']->warning("WARNING" . $string);
        $this->app['monolog.mailer']->error("ERROR" . $string);
        
        $this->assertFileExists(dirname($this->app['monolog.logfile']) . '/test.mailer.log');
        $content = file_get_contents($filepath);
        $this->assertInternalType('int', strpos($content, "INFO" . $string));
        $this->assertInternalType('int', strpos($content, "WARNING" . $string));
        $this->assertInternalType('int', strpos($content, "ERROR" . $string));
    }
}
