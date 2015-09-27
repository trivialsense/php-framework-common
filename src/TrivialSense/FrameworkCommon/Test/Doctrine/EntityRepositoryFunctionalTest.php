<?php

/*
 * This file is part of php-framework-common
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace TrivialSense\FrameworkCommon\Test\Doctrine;

use TrivialSense\FrameworkCommon\Test\Symfony\FunctionalTest;
use Doctrine\ORM\EntityRepository;

abstract class EntityRepositoryFunctionalTest extends FunctionalTest
{
    /**
     * @var EntityRepository
     */
    protected static $repository;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$repository = static::getServiceStatic("doctrine")
            ->getManager(static::getTestedEntityManager())->getRepository(static::getTestedRepositoryName());
    }

    protected function loadFixtures(array $classNames, $omName = null, $registryName = 'doctrine', $purgeMode = null)
    {
        if(!is_array($classNames))
            $classNames = array($classNames);

        if (!is_null($this->getDataFixturesNamespace())) {
            foreach ($classNames as $index => $class) {
                $classNames[$index] = $this->getDataFixturesNamespace() . "\\" . $class;
            }
        }

        return parent::loadFixtures($classNames, $omName, $registryName, $purgeMode);
    }

    public function getTestedRepository()
    {
        return self::$repository;
    }

    protected function getDataFixturesNamespace()
    {
        return null;
    }

    protected static function getTestedEntityManager()
    {
        return null;
    }

    public static function hasDatabaseEntities()
    {
        return true;
    }

    protected static function getTestedRepositoryName()
    {
        return '';
    }
}
