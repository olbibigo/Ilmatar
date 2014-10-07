<?php
namespace Project\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class Parameter extends AbstractFixture
{
    const NB_PARAMETERS = 100;

    public function load(ObjectManager $em)
    {
        for ($i = 0;  $i < self::NB_PARAMETERS; ++$i) {
            $param = new \Entities\Parameter();
            $param->setCode($this->generateString(10));
            $param->setCategory('CAT');
            $param->setIsReadonly(true);
            $param->setType($i%4);
            switch($i%4) {
                case \Entities\Parameter::TYPE_STRING:
                    $param->setValue("Hello world!");
                    break;
                case \Entities\Parameter::TYPE_INTEGER:
                    $param->setValue(666);
                    break;
                case \Entities\Parameter::TYPE_FLOAT:
                    $param->setValue(666.66);
                    break;
                case \Entities\Parameter::TYPE_BOOLEAN:
                    $param->setValue(true);
                    break;
                default:
            }
            $em->persist($param);
        }
        $em->flush();
    }

    protected function generateString($length)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }
}