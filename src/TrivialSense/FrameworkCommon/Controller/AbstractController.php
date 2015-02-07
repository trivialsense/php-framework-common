<?php

/*
 * This file is part of php-framework-common
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace TrivialSense\FrameworkCommon\Controller;

use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use TrivialSense\FrameworkCommon\Container\ContainerHelperInterface;
use TrivialSense\FrameworkCommon\Container\ContainerHelperTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abstract controller with many helpers
 */
abstract class AbstractController extends Controller
    implements ContainerHelperInterface
{
    use ContainerHelperTrait;

    /**
     * @var array
     */
    private $loadedServices = [];

    /**
     * @param ContainerInterface $container
     *
     * @return null|void
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->setupController();
    }

    /**
     * Override this to initialize your controller
     */
    protected function setupController()
    {
    }

    /**
     * @param string $id Service name/id
     *
     * @return object
     */
    public function get($id)
    {
        if(!isset($this->loadedServices[$id]))
            $this->loadedServices[$id] = parent::get($id);

        return $this->loadedServices[$id];
    }

    /**
     * Returns a ResponseRedirect to a given route name
     *
     * @param $path
     * @param array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToPath($path, $parameters = array())
    {
        $route = $this->getRouter()->generate($path, $parameters);

        return $this->redirect($route);
    }

    /**
     * Sends a Response serving a given file
     *
     * @param $filePath
     * @param string $fileName Custom name, to replace original one
     *
     * @return Response
     */
    public function sendFile($filePath, $fileName = null)
    {
        if ($filePath instanceof File) {
            $filePath = $filePath->getPathname();
        }

        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-Type', mime_content_type($filePath));
        $response->headers->set('Content-Disposition', 'attachment;filename="' . ($fileName ?: basename($filePath)). '"');
        $response->headers->set('Content-Length', filesize($filePath));

        $response->setContent(file_get_contents($filePath));

        return $response;
    }

    /**
     * @return bool
     */
    public function isPost()
    {
        return $this->isMethod("POST");
    }

    /**
     * @param $method
     *
     * @return bool
     */
    public function isMethod($method)
    {
        return $this->getRequest()->isMethod($method);
    }

    /**
     * Creates a submit a form, to use in controllers
     *
     * @param $type
     * @param object|array $data
     * @param callable     $validCallback Callback to call when form is valid
     *
     * @return \Symfony\Component\Form\FormView
     */
    public function createAndSubmitForm($type, $data = null, callable $validCallback = null)
    {
        $form = $this->createForm($type, $data);

        if ($this->isPost()) {
            $form->handleRequest($this->getRequest());

            if ($form->isValid()) {
                if ($validCallback) {
                    $response = $validCallback($form->getData(), $form);

                    if($response instanceof Response)

                        return $response;
                }
            }
        }

        return $form->createView();
    }

    /**
     * Gets a parameter
     *
     * @param $name
     *
     * @return array|string
     */
    public function parameter($name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * @return \Swift_Mailer
     */
    public function getMailer()
    {
        return $this->service('mailer');
    }

    public function sendMail($message)
    {
        return $this->getMailer()->send($message);
    }

    public function addSuccessMessage($message, $translateParams = array())
    {
        $this->getSession()->getFlashBag()->add('success', $this->trans($message, $translateParams));
    }

    public function getTranslator()
    {
        return $this->service('translator');
    }

    public function trans($message, array $params = array())
    {
        return $this->getTranslator()->trans($message, $params);
    }

    /**
     * @return CsrfTokenManagerInterface
     */
    protected function getCsrfProvider()
    {
        return $this->service('form.csrf_provider');
    }
}
