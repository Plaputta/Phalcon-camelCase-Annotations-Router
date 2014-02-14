<?php

namespace TestProject\Frontend\Controllers;
use Phalcon\Mvc\Controller,
    Phalcon\Tag as Tag;
/**
 * Class ErrorController
 * @package TestProject\Frontend\Controllers
 *
 * @RoutePrefix("/error")
 *
 */
class ErrorController extends Controller
{

    public function initialize()
    {

    }
    /**
     * @Get("/404", name="pageNotFound")
     *
     * Example for setting other status codes than 200.
     *
     */
    public function pageNotFoundAction()
    {
        $this->response->setStatusCode(404, "Page not found");
    }

}

