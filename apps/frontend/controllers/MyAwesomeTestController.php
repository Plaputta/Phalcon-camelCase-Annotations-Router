<?php
namespace TestProject\Frontend\Controllers;
use Phalcon\Mvc\Controller,
    Phalcon\Tag as Tag;

/**
 * Class MyAweseomeTestController
 * @package TestProject\Frontend\Controllers
 *
 * @RoutePrefix("/awesome-test")
 *
 */
class MyAwesomeTestController extends Controller
{
    /**
     * @Get("/super-action", name="logout")
     *
     */
    public function mySuperTestAction()
    {
       $this->view->setVar('date', date('Y-m-d'));
    }

    /**
     * @Get("/forward-test")
     */
    public function forwardTestAction()
    {
        $this->dispatcher->forward(array(
            'controller' => 'myAwesomeTest',
            'action' => 'mySuperTest'
        ));
    }

}

