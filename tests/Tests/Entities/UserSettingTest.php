<?php
namespace Tests\Entities;

use Ilmatar\Tests\AbstractTestCase;
use Entities\UserSetting;

class UserSettingTest extends AbstractTestCase
{
    /**
     * @group UserSettingTest
     * @group UserSettingTest::testDefaultSettings
     */
    public function testDefaultSettings()
    {
        $availableTypes =  array_keys(\Entities\Parameter::getAllTypes());

        $settings = UserSetting::getDefaultSettings(true);
        foreach ($settings as $setting) {
            $this->assertTrue(in_array($setting['type'], $availableTypes));
            switch($setting['type']) {
                case \Entities\Parameter::TYPE_BOOLEAN:
                    $this->assertTrue(is_bool($setting['value']));
                    break;
                case \Entities\Parameter::TYPE_STRING:
                    $this->assertTrue(is_string($setting['value']));
                    break;
                case \Entities\Parameter::TYPE_INTEGER:
                    $this->assertTrue(is_int($setting['value']));
                    break;
                case \Entities\Parameter::TYPE_FLOAT:
                    $this->assertTrue(is_float($setting['value']));
                    break;
                default:
                    //Nothing
            }
        }
    }
    /**
     * @group UserSettingTest
     * @group UserSettingTest::testGetUserSettings
     */
    public function testGetUserSettings()
    {
        $defaultSettings = UserSetting::getDefaultSettings(false);
        $settings = $this->app['orm.em']->find('\\Entities\\User', 1)->getSettings();
        foreach ($defaultSettings as $code => $value) {
            $this->assertEquals($value, $settings[$code]);
        }
    }
    
    /**
     * @group UserSettingTest
     * @group UserSettingTest::testSetUserSettings
     */
    public function testSetUserSettings()
    {
        $userRepo    = $this->app['orm.em']->getRepository('\\Entities\\User');
        $settingRepo = $this->app['orm.em']->getRepository('\\Entities\\UserSetting');
        $user        = $this->app['orm.em']->find('\\Entities\\User', 1);

        $this->assertNull(
            $settingRepo->findOneBy(['code' => UserSetting::LANDING_PAGE, 'user' => $user])
        );
        //Changes value for not default one
        $userRepo->setUserSettings(
            $user,
            [UserSetting::LANDING_PAGE => 'xxx'],
            true
        );
        $setting = $settingRepo->findOneBy(
            [
                'code' => UserSetting::LANDING_PAGE,
                'user' => $user
            ]
        );
        $this->assertInstanceOf('\Entities\UserSetting', $setting);
        $this->assertEquals('xxx', $setting->getValue());
        
        $this->app['orm.em']->refresh($user);//necessary to refresh association with previous user setting
        //Resets
        $userRepo->setUserSettings(
            $user,
            [UserSetting::LANDING_PAGE => UserSetting::getDefaultSettings()[UserSetting::LANDING_PAGE]],
            true
        );
        $this->assertNull(
            $settingRepo->findOneBy(['code' => UserSetting::LANDING_PAGE, 'user' => $user])
        );
    }
}
