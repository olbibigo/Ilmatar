<?php
namespace Tests\Ilmatar;

use Ilmatar\Tests\AbstractTestCase;
use Carbon\Carbon;

class AuditableListenerTest extends AbstractTestCase
{
    /**
     * @group AuditableListenerTest
     * @group AuditableListenerTest::testAudit
     */
    public function testAudit()
    {
        $em = $this->app['orm.em'];
        
        //Audited entity
        $pipo     = $em->find('\\Entities\\Pipo', 1);
        $oldValue = $pipo->getEmail();
        $newValue = 'new@value.com';
        $pipo->setEmail($newValue);//audited field
        $oldUser = $pipo->getUser();
        $pipo->setUser($em->find('\\Entities\\User', 10));//audited field
        $pipo->setValue($pipo->getValue());//audited field but same value
        $pipo->setThedatetimeAt(Carbon::now());//not audited field
        $newDate = Carbon::now()->subDay();
        $pipo->setThetypeDate($newDate);//audited field
        $em->persist($pipo);
        
        //Not audited entity
        $user = $em->find('\\Entities\\User', 1);
        $user->setFirstname('newname');
        $em->persist($user);
        
        $em->flush();
        
        $audits = $em->getRepository('\\Entities\\Audit')->findBy(['entity_type' => 'Entities\Pipo', 'entity_id' => 1]);
        $this->assertCount(3, $audits);
        foreach ($audits as $audit) {
            $fieldName = $audit->getPropertyName();
            $this->assertTrue(in_array($audit->getPropertyName(), ['email', 'user', 'thetype_date']));
            $this->assertEquals('Console', $audit->getCreatedBy());
            if ('email' == $fieldName) {
                $this->assertEquals($oldValue, $audit->getOldValue());
                $this->assertEquals($newValue, $audit->getNewValue());
            }
            if ('user' == $fieldName) {
                $this->assertEquals($oldUser->getId(), $audit->getOldValue());
                $this->assertEquals(10, $audit->getNewValue());
            }
            if ('thetype_date' == $fieldName) {
                $this->assertEquals($newDate->toISO8601String(), $audit->getNewValue());
            }
        }
        $this->assertEmpty($em->getRepository('\\Entities\\Audit')->findBy(['entity_type' => 'Entities\User', 'entity_id' => 1]));
    }
}
