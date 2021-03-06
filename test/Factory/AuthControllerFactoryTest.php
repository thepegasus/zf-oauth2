<?php
/**
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

namespace ZFTest\OAuth2\Factory;

use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use ZF\OAuth2\Controller\AuthController;
use ZF\OAuth2\Factory\AuthControllerFactory;

class AuthControllerFactoryTest extends AbstractHttpControllerTestCase
{
    /**
     * @var ControllerManager
     */
    protected $controllers;

    /**
     * @var AuthControllerFactory
     */
    protected $factory;

    /**
     * @var ServiceManager
     */
    protected $services;

    public function testControllerCreated()
    {
        $oauthServerFactory = function () {
        };
        $this->services->setService('ZF\OAuth2\Service\OAuth2Server', $oauthServerFactory);

        $userIdProvider = $this->getMockBuilder('ZF\OAuth2\Provider\UserId\UserIdProviderInterface')->getMock();
        $this->services->setService('ZF\OAuth2\Provider\UserId', $userIdProvider);

        $controller = $this->isV2ServiceManager($this->services)
            ? $controller = $this->factory->createService($this->controllers)
            : $controller = $this->factory->__invoke($this->services, AuthController::class);

        $this->assertInstanceOf('ZF\OAuth2\Controller\AuthController', $controller);
        $this->assertEquals(new AuthController($oauthServerFactory, $userIdProvider), $controller);
    }

    protected function setUp()
    {
        $this->factory = new AuthControllerFactory();

        $this->services = $services = new ServiceManager();

        $this->services->setService('config', [
            'zf-oauth2' => [
                'api_problem_error_response' => true,
            ],
        ]);
        $sm = new ServiceManager();
        $sm->setService('ServiceManager', $services);

        $this->controllers = $controllers = new ControllerManager($this->services);

        $this->setApplicationConfig([
            'modules' => [
                'ZF\OAuth2',
            ],
            'module_listener_options' => [
                'module_paths' => [__DIR__ . '/../../'],
                'config_glob_paths' => [],
            ],
            'service_listener_options' => [],
            'service_manager' => [],
        ]);
        parent::setUp();
    }

    protected function isV2ServiceManager($services)
    {
        return (! method_exists($services, 'configure'));
    }
}
