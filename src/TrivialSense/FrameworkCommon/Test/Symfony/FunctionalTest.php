<?php

/*
 * This file is part of php-framework-common
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace TrivialSense\FrameworkCommon\Test\Symfony;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use TrivialSense\FrameworkCommon\Container\ContainerHelperInterface;
use TrivialSense\FrameworkCommon\Container\ContainerHelperTrait;
use TrivialSense\FrameworkCommon\Test\File\DummyFile;
use TrivialSense\FrameworkCommon\Test\File\DummyUploadedFile;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Providers many helpers to aid your functional tests
 */
abstract class FunctionalTest extends WebTestCase
    implements ContainerHelperInterface
{
    use ContainerHelperTrait;

    /**
     * @var Application
     */
    protected static $application;

    /**
     * @var Client
     */
    protected static $client;

    /**
     * @var ContainerInterface
     */
    protected static $container;

    /**
     * @param $name
     * @param array $params
     * @return int Command exit code
     * @throws \Exception
     *
     */
    private static function runCommandStatic($name, array $params = array())
    {
        array_unshift($params, $name);

        $input = new ArrayInput($params);
        $input->setInteractive(false);

        $fp = fopen('php://temp/maxmemory', 'r+');
        $output = new StreamOutput($fp);

        self::getApplication()->run($input, $output);

        rewind($fp);

        $returnOutput = stream_get_contents($fp);

        return $returnOutput;
    }

    /**
     * @return Application
     */
    private static function getApplication()
    {
        if (null === self::$application) {
            self::$client = static::createClient();
            self::$application = new Application(self::$kernel);
            self::$application->setAutoExit(false);
        }

        return self::$application;
    }

    /**
     * Set ups everything before the class, bootstrap Symfony2 client,
     * setup database entities, etc.
     */
    public static function setUpBeforeClass()
    {
        if (static::hasDatabaseEntities()) {
            self::runCommandStatic('doctrine:database:create');
            self::runCommandStatic('doctrine:schema:update', array('--force' => true));
        }

        // create Symfony2 client
        self::$client = static::createClient();
        self::$container = self::$client->getContainer();
    }

    /**
     * Cleans database and shutsdown kernel
     */
    public static function tearDownAfterClass()
    {
        if (static::hasDatabaseEntities()) {
            self::runCommandStatic('doctrine:database:drop', array('--env' => 'test', '--force' => true));
        }

        if (null !== static::$kernel) {
            static::$kernel->shutdown();
        }
    }

    /**
     * @param string $name
     * @param array $params
     * @param bool $reuseKernel
     * @return int Command exit code
     */
    protected function runCommand($name, array $params = array(), $reuseKernel = false)
    {
        return self::runCommandStatic($name, $params);
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return self::$container;
    }

    /**
     * @return ContainerInterface
     */
    protected static function getContainerStatic()
    {
        return self::$container;
    }

    /**
     * @return bool
     */
    protected static function hasDatabaseEntities()
    {
        return false;
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        return self::$client;
    }

    /**
     * Gets and user from repository database
     *
     * @param $repository
     * @param $username
     * @param string $userField
     *
     * @return mixed
     */
    protected function getUserFromDatabase($repository, $username, $userField = 'username')
    {
        return $this->createQuery("SELECT u FROM " .$repository. " u WHERE u." .$userField. " = '" .$username. "'")
            ->getSingleResult();
    }

    /**
     * Logs user againts a given firewall
     *
     * @param UserInterface $user
     * @param string        $firewall
     */
    protected function loginUser(UserInterface $user, $firewall = "main")
    {
        $token = $this->createUserToken($user);

        $session = $this->getService('session');
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();

        $this->getClient()->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
    }

    /**
     * Creates a user Token and sets it to the SecurityContext
     *
     * @param UserInterface $user
     * @param string        $firewall
     *
     * @return UsernamePasswordToken
     */
    protected function createUserToken(UserInterface $user, $firewall = "main")
    {
        $token = new UsernamePasswordToken($user, $user->getPassword(), $firewall, $user->getRoles());

        $this->getSecurityContext()->setToken($token);

        return $token;
    }

    /**
     * Creates a dummy file to testing purposes
     *
     * @param $file
     *
     * @return DummyFile
     */
    protected function createDummyFile($file)
    {
        $file = new File($file);

        $dummyFile = $file->getFileInfo()->getBasename("." . $file->getExtension()) . time() . "." . $file->getExtension();
        $dummyFile = $this->getParameter("kernel.cache_dir") . "/" . $dummyFile;

        copy($file->getPathname(), $dummyFile);

        return new DummyFile($dummyFile);
    }

    /**
     * Creates a dummy file for upload tests
     *
     * @param $path
     *
     * @return DummyUploadedFile
     */
    protected function createDummyUploadedFile($path)
    {
        $dummyFile = $this->createDummyFile($path);

        return DummyUploadedFile::createFromFile($dummyFile);
    }

    /**
     * @param $name
     * @return string
     */
    protected function generateRoute($name)
    {
        return $this->getRouter()->generate($name);
    }

    /**
     * Assert a response redirects to a given route name
     *
     * @param $expectedRoutePath
     * @param Response $actualResponse
     * @param array    $parameters
     * @param int      $expectedStatusCode
     */
    protected function assertResponseRedirect($expectedRoutePath, Response $actualResponse, array $parameters = array(), $expectedStatusCode = 302)
    {
        $expectedRedirection = $this->getRouter()->generate($expectedRoutePath, $parameters);

        $this->assertEquals($expectedStatusCode, $actualResponse->getStatusCode());
        $this->assertEquals($expectedRedirection, $actualResponse->headers->get("Location"));
    }

    /**
     * Request a give route name
     *
     * @param $name
     * @param string $method
     * @param bool   $ajax
     * @param array  $parameters
     * @param array  $files
     * @param array  $server
     * @param null   $content
     * @param bool   $changeHistory
     *
     * @return null|Response
     */
    protected function requestRoute($name, $method = "GET", $ajax = false, array $parameters = array(), array $files = array(), array $server = array(), $content = null, $changeHistory = true)
    {
        if ($ajax) {
            $server = array_merge($server, array('HTTP_X-Requested-With' => 'XMLHttpRequest'));
        }

        $this->getClient()->request($method, $this->generateRoute($name), $parameters, $files, $server, $content, $changeHistory);

        return $this->getClient()->getResponse();
    }

    /**
     * Request a file upload to a give route
     *
     * @param $name
     * @param UploadedFile $file
     * @param bool         $ajax
     * @param string       $fieldName
     * @param array        $parameters
     *
     * @return null|Response
     */
    protected function requestUploadFileRoute($name, UploadedFile $file, $ajax = false, $fieldName = "file", $parameters = array())
    {
        return $this->requestRoute($name, "POST", $ajax, $parameters, array($fieldName => $file));
    }

    /**
     * Assert a JSON response matches expected data.
     *
     * @param string|object|array $expectedResponse
     * @param Response            $actualResponse
     * @param int                 $expectedStatusCode
     */
    protected function assertJsonResponse($expectedResponse, Response $actualResponse, $expectedStatusCode = 200)
    {
        if(is_object($expectedResponse) || is_array($expectedResponse))
            $expectedResponse = json_encode($expectedResponse);

        $this->assertEquals($expectedStatusCode, $actualResponse->getStatusCode());
        $this->assertEquals($expectedResponse, $actualResponse->getContent());
    }

    /**
     * @param array $roles
     *
     * @return AnonymousToken
     */
    protected function loginAnonymousUser($roles = array())
    {
        $token = new AnonymousToken("anon", "anon", $roles);
        $this->getSecurityContext()->setToken($token);

        return $token;
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        return self::$container->get($id);
    }

    /**
     * {@inheritDoc}
     */
    public function parameter($name)
    {
        return self::$container->getParameter($name);
    }

    /**
     * Gets a service in static context
     *
     * @param $id
     * @return object
     */
    public static function getServiceStatic($id)
    {
        return self::$container->get($id);
    }
}
