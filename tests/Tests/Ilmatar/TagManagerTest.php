<?php
namespace Tests\Ilmatar;

use Ilmatar\Tests\AbstractTestCase;

class TestManagerTest extends AbstractTestCase
{
    /**
     * @group TestManagerTest
     * @group TestManagerTest::testReplaceTag
     */
    public function testReplaceTag()
    {
        $tagManager = new \Ilmatar\TagManager(
            $this->app['app.tags.strategies'],
            array(
                'password'     => 'NEW_PASSWORD',
                'user'         => $this->app['orm.em']->find('\\Entities\\User', 1)
            )
        );
        $replaced = $tagManager->replaceTags("%%user_name%%, this is your new password %%user_password%% %%user_password%%");
        $this->assertEquals("A. Nistrateur, this is your new password NEW_PASSWORD NEW_PASSWORD", $replaced);
    }
}
