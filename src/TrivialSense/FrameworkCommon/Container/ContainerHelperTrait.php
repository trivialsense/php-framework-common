<?php

/*
 * This file is part of php-framework-common
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace TrivialSense\FrameworkCommon\Container;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Helper to container aware services, classes, etc.
 *
 * Helpers using this trait must implement ContainerAwareInterface
 */
trait ContainerHelperTrait
{
    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->get("session");
    }

    /**
     * @return Registry
     */
    public function getDoctrine()
    {
        return $this->get("doctrine");
    }

    /**
     * @param string $name
     *
     * @return EntityManager
     */
    public function getEntityManager($name = null)
    {
        return $this->getDoctrine()->getManager($name);
    }

    /**
     * @param $dql
     * @param string $entityManager
     *
     * @return \Doctrine\ORM\Query
     */
    public function createQuery($dql, $entityManager = null)
    {
        return $this->getEntityManager($entityManager)->createQuery($dql);
    }

    /**
     * @param $dql
     * @param array  $parameters
     * @param string $entityManager
     *
     * @return array|int|object
     */
    public function executeQuery($dql, $parameters = array(), $entityManager = null)
    {
        return $this->createQuery($dql, $entityManager)->execute($parameters);
    }

    /**
     * @param $name
     * @param string $entityManager
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository($name, $entityManager = null)
    {
        return $this->getEntityManager($entityManager)->getRepository($name);
    }

    /**
     * Persist and flush a given entity
     *
     * @param $entity
     *
     * @param string $entityManager
     */
    public function persistAndFlush($entity, $persistAll = false, $entityManager = null)
    {
        $entityManager = $this->getEntityManager($entityManager);

        $entityManager->persist($entity);
        $entityManager->flush($persistAll ? null : $entity);
    }

    public function persist($object)
    {
        $this->getEntityManager()->persist($object);
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }

    /**
     * Remove and flush a given entity
     *
     * @param $entity
     *
     * @param string $entityManager
     */
    public function deleteAndFlush($entity, $entityManager = null)
    {
        $entityManager = $this->getEntityManager($entityManager);

        $entityManager->remove($entity);
        $entityManager->flush($entity);
    }

    /**
     * @param string $name
     *
     * @return object
     */
    public function getService($name)
    {
        return $this->service($name);
    }

    /**
     * @param $name
     *
     * @return array|string
     */
    public function getParameter($name)
    {
        return $this->parameter($name);
    }

    /**
     * Check if a role is granted for the current user
     *
     * @param $role
     *
     * @return bool
     */
    public function isGrantedRole($role)
    {
        return $this->getAuthorizationChecker()->isGranted($role);
    }

    /**
     * @return AuthorizationChecker
     */
    public function getAuthorizationChecker()
    {
        return $this->get("security.authorization_checker");
    }

    /**
     * @return TokenStorage
     */
    public function getTokenStorage()
    {
        return $this->get('security.token_storage');
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->get("router");
    }

    /**
     * Gets the kernel cache directory
     *
     * @param string $append Optionally append a custom directory
     *
     * @return string
     */
    public function getCacheDir($append = '')
    {
        return $this->parameter('kernel.cache_dir') . $append;
    }

    /**
     * @param $name
     *
     * @return object
     */
    public function service($name)
    {
        return $this->get($name);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->get("request");
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function request()
    {
        return $this->getRequest();
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->service('event_dispatcher');
    }

    public function dispatchEvent($eventName, Event $event)
    {
        return $this->getEventDispatcher()->dispatch($eventName, $event);
    }

    public function listenEvent($eventName, callable $callable, $priority = 1)
    {
        $this->getEventDispatcher()->addListener($eventName, $callable, $priority);
    }
}
