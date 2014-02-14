<?php


use Phalcon\Mvc\Router,
    Phalcon\DI\FactoryDefault;


$di['router'] = function () {

    $router = new \Nischenspringer\Mvc\Router\Annotations(false);

    $router->setUriSource(\Phalcon\Mvc\Router::URI_SOURCE_SERVER_REQUEST_URI);
    $router->removeExtraSlashes(true);

    $router->notFound(
        array(
            'namespace' => 'TestProject\Frontend\Controllers',
            'module' => 'frontend',
            'controller' => 'error',
            'action' => 'pageNotFound'
        )
    );

    $router->addModuleResource('frontend', 'TestProject\Frontend\Controllers\Index');
    $router->addModuleResource('frontend', 'TestProject\Frontend\Controllers\MyAwesomeTest');
    $router->addModuleResource('frontend', 'TestProject\Frontend\Controllers\Error');

    return $router;
};

$di['annotations'] = function () {
    return new \Phalcon\Annotations\Adapter\Memory();
};