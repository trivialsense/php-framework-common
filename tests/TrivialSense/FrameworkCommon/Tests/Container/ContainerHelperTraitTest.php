<?php

/*
 * This file is part of php-framework-common
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace TrivialSense\FrameworkCommon\Tests\Container;

use Symfony\Component\Security\Core\SecurityContext;
use TrivialSense\FrameworkCommon\Controller\AbstractController;
use TrivialSense\FrameworkCommon\Test\Security\DummyUser;
use TrivialSense\FrameworkCommon\Test\Symfony\FunctionalTest;
use TrivialSense\FrameworkCommon\Tests\Bundle\TestBundle\Entity\TestEntity;
use TrivialSense\FrameworkCommon\Tests\Controller\MockupAbstractController;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;

class ContainerHelperTraitTest extends FunctionalTest
{
    /**
     * @var MockupAbstractController
     */
    protected static $controller;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$controller = new MockupContainerHelperTrait();
        self::$controller->setContainer(self::$container);
    }

    public function testGetDoctrine()
    {
        $doctrine = $this->getController()->getDoctrine();

        $this->assertTrue($doctrine instanceof Registry);
    }

    public function testGetDefaultEntityManager()
    {
        $entitymanager = $this->getController()->getEntityManager();

        $this->assertTrue($entitymanager instanceof EntityManager);
    }

    public function testGetNonDefaultEntityManager()
    {
        $entityManager = $this->getController()->getEntityManager("test");

        $this->assertTrue($entityManager instanceof EntityManager);
    }

    public function testGetRepository()
    {
        $repository = $this->getController()->getRepository("FrameworkCommonTestBundle:TestEntity");

        $this->assertTrue($repository instanceof EntityRepository);
    }

    public function testCreateQuery()
    {
        $dql = "SELECT u FROM FrameworkCommonTestBundle:TestEntity u";

        $query = $this->getController()->createQuery($dql);

        $this->assertTrue($query instanceof Query);
        $this->assertEquals($dql, $query->getDQL());
    }

    public function testExecuteQuery()
    {
        $dql = "SELECT u FROM FrameworkCommonTestBundle:TestEntity u";

        $query = $this->getController()->executeQuery($dql);

        $this->assertEmpty($query);
    }

    public function testGetParameter()
    {
        $parameter = $this->getController()->getParameter("test");

        $this->assertEquals("testing_this", $parameter);
    }

    public function testPersistDeleteAndFlush()
    {
        $entity = new TestEntity();
        $entity->setTestField("persist_and_flush");

        $this->getController()->persistAndFlush($entity);

        $dql = "SELECT u FROM FrameworkCommonTestBundle:TestEntity u";

        $result = $this->getController()->executeQuery($dql);
        $entityResult = $result[0];

        $this->assertTrue($entityResult instanceof TestEntity);
        $this->assertEquals($entity, $entityResult);

        $this->getController()->deleteAndFlush($entity);

        $dql = "SELECT u FROM FrameworkCommonTestBundle:TestEntity u";

        $result = $this->getController()->executeQuery($dql);

        $this->assertEmpty($result);
    }

    public function testGetService()
    {
        $service = $this->getController()->getService("security.context");

        $this->assertTrue($service instanceof SecurityContext);
    }

    public function testIsGrantedAnonymousUser()
    {
        $this->loginAnonymousUser(array("ROLE_EXAMPLE"));

        $granted = $this->isGranted("ROLE_EXAMPLE");

        $this->assertTrue($granted);
    }

    public function testIsGrantedLoggedUser()
    {
        $dummyUser = new DummyUser(array("ROLE_CUSTOM"));

        $this->loginUser($dummyUser);

        $granted = $this->isGranted("ROLE_CUSTOM");

        $this->assertTrue($granted);
    }

    public function testGetRouter()
    {
        $router = $this->getRouter();

        $this->assertTrue($router instanceof Router);
    }

    public function testGetCacheDir()
    {
        $cacheDir = $this->getCacheDir();

        $this->assertEquals($this->getParameter("kernel.cache_dir"), $cacheDir);
    }

    public function testGetCacheDirAppended()
    {
        $cacheDir = $this->getCacheDir("/test/cache/directory");

        $this->assertEquals($this->getParameter("kernel.cache_dir") . "/test/cache/directory", $cacheDir);
    }

    public function testGetRequest()
    {
        $dummyRequest = Request::createFromGlobals();
        $this->getContainer()->enterScope("request");
        $this->getContainer()->set("request", $dummyRequest);

        $request = $this->getRequest();
        $requestBis = $this->request();

        $this->assertTrue($request instanceof Request);
        $this->assertTrue($requestBis instanceof Request);
    }

    public function testGetSession()
    {
        $session = $this->getSession();

        $this->assertTrue($session instanceof Session);
    }

    protected function getController()
    {
        return self::$controller;
    }

    public static function hasDatabaseEntities()
    {
        return true;
    }
}

/**
 * Mockup an AbstractController to test the trait methods
 */
class MockupContainerHelperTrait extends AbstractController
{
}