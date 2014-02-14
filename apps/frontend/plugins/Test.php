<?php

namespace TestProject\Frontend\Plugins;

use Phalcon\Events\Event,
    Phalcon\Mvc\User\Plugin,
    Phalcon\Mvc\Dispatcher,
    Phalcon\Acl,
    Phalcon;

/**
 * Class Test
 * @package TestProject\Frontend\Plugins
 *
 */
class Test extends Plugin
{
    public function beforeDispatch(Event $event, Dispatcher $dispatcher)
    {
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();

        header('X-Controller: '.$controller);
        header('X-Action: '.$action);
    }

}
