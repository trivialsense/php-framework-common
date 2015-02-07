<?php

/*
 * This file is part of php-framework-common
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace TrivialSense\FrameworkCommon\Tests\Test;

use TrivialSense\FrameworkCommon\Test\Symfony\FunctionalTest;

class FunctionalTestTest extends FunctionalTest
{
    public function testRunCommand()
    {
        $result = $this->runCommand("trivialsense:test");

        $this->assertEquals('', $result);
    }

    public function testLoadFixtures()
    {
        $this->loadFixtures(array(
            'TrivialSense\FrameworkCommon\Tests\Bundle\TestBundle\DataFixtures\ORM\TestEntityFixture'
        ));

        $result = $this->executeQuery("SELECT u FROM FrameworkCommonTestBundle:TestEntity u");
        $resultEntity = $result[0];

        $this->assertEquals("test", $resultEntity->getTestField());
    }

    public static function hasDatabaseEntities()
    {
        return true;
    }
}