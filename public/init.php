<?php
use Phalcon\Mvc\Application,
    Phalcon\DI\FactoryDefault;

/**
 * Global autoloader for router annotations
 */
$loader = new Phalcon\Loader();

require CONFIG_PROJECT_PATH . 'config/loader.php';

$loader->register();

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();
/**
 * Include services
 */
require CONFIG_PROJECT_PATH . 'config/services.php';

/**
 * Handle the request
 */
$application = new Application();

/**
 * Include modules
 */
require(CONFIG_PROJECT_PATH . 'config/modules.php');


//Registering a dispatcher
$di->set('dispatcher', function () {
    $dispatcher = new \Phalcon\Mvc\Dispatcher();
    $dispatcher->setDefaultNamespace('TestProject\Frontend\Controllers');

    return $dispatcher;
});

/**
 * Assign the DI
 */
$application->setDI($di);
