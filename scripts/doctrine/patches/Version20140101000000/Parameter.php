<?php
namespace Version20140101000000;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class Parameter extends AbstractFixture
{
    public function load(ObjectManager $em)
    {
        $param = new \Entities\Parameter();
        $param->setCode(\Entities\Parameter::ANALYTICS_SNIPPET);
        $param->setCategory('ANALYTICS');
        $param->setType(\Entities\Parameter::TYPE_STRING);
        $param->setValue('<script type="text/javascript">var _gaq = _gaq || []; _gaq.push(["_setAccount", "UA-XXXXX-X"]); _gaq.push(["_trackPageview"]); (function() { var ga = document.createElement("script"); ga.type = "text/javascript"; ga.async = true; ga.src = ("https:" == document.location.protocol ? "https://ssl" : "http://www") + ".google-analytics.com/ga.js"; var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ga, s); })();</script>');
        $em->persist($param);

        $em->flush();
    }
}