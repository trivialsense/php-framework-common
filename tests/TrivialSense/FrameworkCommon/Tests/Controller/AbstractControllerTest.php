<?php

/*
 * This file is part of php-framework-common
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace TrivialSense\FrameworkCommon\Tests\Controller;

use TrivialSense\FrameworkCommon\Controller\AbstractController;
use TrivialSense\FrameworkCommon\Test\Symfony\FunctionalTest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class AbstractControllerTest extends FunctionalTest
{
    /**
     * @var MockupAbstractController
     */
    protected static $controller;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$controller = new MockupAbstractController();
        self::$controller->setContainer(self::$container);
    }

    protected function setUp()
    {
        parent::setUp();

        self::$container->enterScope("request");
        self::$container->set("request", new Request());
    }

    protected function tearDown()
    {
        self::$container->leaveScope('request');

        parent::tearDown();
    }

    public function testSetupControllerIsCalled()
    {
        $this->getController()->setContainer($this->getContainer());
        $this->assertTrue($this->getController()->called);
    }

    public function testGetServiceShorcut()
    {
        $service = $this->get("security.context");

        $this->assertTrue($service instanceof SecurityContext);

        $serviceBis = $this->get("security.context");

        $this->assertTrue($service === $serviceBis);
    }

    public function testRedirectToPath()
    {
        $path = "test_route";

        $response = $this->getController()->redirectToPath($path);

        $this->assertResponseRedirect($path, $response);
    }

    public function testRedirectToPathWithParams()
    {
        $path = "test_route_params";

        $response = $this->getController()->redirectToPath($path, array('param' => 'nice'));

        $this->assertResponseRedirect($path, $response, array('param' => 'nice'));
    }

    public function testSendFile()
    {
        $dummyFile = $this->createDummyFile(__DIR__ . '/../Resources/example.pdf');

        $response = $this->getController()->sendFile($dummyFile);

        $cacheControl = $response->headers->get("Cache-Control");
        $contentType = $response->headers->get("Content-type");
        $contentDisposition = $response->headers->get("Content-Disposition");
        $contentLength = $response->headers->get("Content-length");
        $content = $response->getContent();

        $this->assertEquals("private", $cacheControl);
        $this->assertEquals("application/pdf", $contentType);
        $this->assertEquals("attachment;filename=\"" . $dummyFile->getFilename(). "\"", $contentDisposition);
        $this->assertEquals(filesize($dummyFile), $contentLength);
        $this->assertEquals(file_get_contents($dummyFile), $content);
    }

    public function testIsPost()
    {
        $this->getRequest()->setMethod("POST");

        $this->assertTrue($this->getController()->isPost());
    }

    public function testCreateAndSubmitFormSimpleType()
    {
        $view = $this->getController()->createAndSubmitForm(new DummyType());

        $this->assertEquals("dummy_type", $view->vars['id']);
    }

    public function testCreateAndSubmitFormSubmitCallback()
    {
        $callback = function(\stdClass $data)
        {
            $this->assertEquals("test", $data->dummy);
        };

        $data = new \stdClass();
        $data->dummy = "test";

        $type = new DummyType();

        // make post request
        $this->getRequest()->setMethod("POST");
        $this->getRequest()->request->set("dummy_type", "");

        $this->getController()->createAndSubmitForm($type, $data, $callback);
    }

    /**
     * @return MockupAbstractController
     */
    protected function getController()
    {
        return self::$controller;
    }
}

class MockupAbstractController extends AbstractController
{
    public $called = false;

    public function setupController()
    {
        $this->called = true;
    }
}

class DummyType extends AbstractType
{

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'dummy_type';
    }
}