<?php

/*
 * This file is part of php-framework-common
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace TrivialSense\FrameworkCommon\Tests\Bundle\TestBundle\DataFixtures\ORM;

use TrivialSense\FrameworkCommon\Tests\Bundle\TestBundle\Entity\TestEntity;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TestEntityFixture implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    function load(ObjectManager $manager)
    {
        $testEntity = new TestEntity();
        $testEntity->setTestField("test");

        $manager->persist($testEntity);
        $manager->flush();
    }
}