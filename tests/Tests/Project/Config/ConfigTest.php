<?php
namespace Tests\Project\Config;

use Ilmatar\Tests\AbstractTestCase;
use Symfony\Component\Config\Util\XmlUtils;

class InstallationTest extends AbstractTestCase
{
    /**
     * Check if all config are the same
     *
     * @return void
     *
     * @group InstallationTest
     * @group InstallationTest::testConfigs
     */
    public function testConfigs()
    {
        $devConfig = XmlUtils::convertDomElementToArray(XmlUtils::loadFile($this->app['app.root'] . '/config/env/dev.xml')->firstChild);
        foreach (glob($this->app['app.root'] . '/config/env/*.xml') as $file) {
            if ($file != 'dev.xml') {
                $config = XmlUtils::convertDomElementToArray(XmlUtils::loadFile($file)->firstChild);
                foreach ($devConfig as $k => $v) {
                    $this->assertTrue(isset($config[$k]), sprintf('Key "%s" is missing in "%s" config file', $k, $file));
                }
            }
        }
    }
}
