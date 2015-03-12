<?php

/**
 * ErrorController 
 */
namespace Molotov\Core\Controllers;
class ErrorController extends \Phalcon\Mvc\Controller
{
    public function show404Action()
    {
        $this->response->setHeader(404, 'Not Found');
        $this->view->pick('404/404');
    }
}