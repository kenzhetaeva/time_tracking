<?php
declare(strict_types=1);

//use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;

class IndexController extends ControllerBase
{

    public function indexAction()
    {
        $this->authorized();
        return $this->response->redirect('/mainpage');

    }

    public function mainPageAction() {
        $this->authorized();
    }

    public function checkAdminAction() {

    }

    public function show404Action() {

    }

    public function show503Action() {

    }

}

