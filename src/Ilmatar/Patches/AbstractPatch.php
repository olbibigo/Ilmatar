<?php
namespace Ilmatar\Patches;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

abstract class AbstractPatch extends AbstractFixture implements OrderedFixtureInterface
{
}
