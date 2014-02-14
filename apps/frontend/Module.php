<?php

namespace TestProject\Frontend;

use Phalcon\Loader,
    Phalcon\Mvc\View,
    Phalcon\Mvc\Url as UrlResolver,
    Phalcon\Mvc\ModuleDefinitionInterface,
    Phalcon\Exception,
    Phalcon\Session\Adapter\Cache as SessionAdapter;

class Module implements ModuleDefinitionInterface
{
    public function registerAutoloaders()
    {
        $loader = new Loader();

        require CONFIG_PROJECT_PATH . 'config/loader.php';

        $loader->registerNamespaces(array(
            'TestProject\Frontend\Plugins' => __DIR__ . '/plugins/'
        ), true);

        $loader->register();

    }

    public function registerServices($di)
    {
        $di['config'] = function () {
            return new \Phalcon\Config(array(
                'application' => array(
                    'controllersDir' => __DIR__ . '/../controllers/',
                    'viewsDir' => __DIR__ . '/../views/'
                )
            ));
        };

        $di['url'] = function () {
            $url = new UrlResolver();
            $url->setBaseUri('/');
            $url->setStaticBaseUri('/');
            return $url;
        };


        $di['view'] = function () {
            $view = new View();
            $view->setViewsDir(__DIR__ . '/views/');
            return $view;
        };

        $di['dispatcher'] = function () use ($di) {

            $eventsManager = $di->getShared('eventsManager');

            $test = new \TestProject\Frontend\Plugins\Test();

            $eventsManager->attach('dispatch', $test);

            $dispatcher = new \Phalcon\Mvc\Dispatcher();
            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        };

    }

}