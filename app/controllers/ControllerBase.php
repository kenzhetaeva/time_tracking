<?php

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{
    // if user is authorized
    public function authorized() {
        if(!$this->isLoggedIn()) {
            return $this->response->redirect('/login');
        }
    }

    public function isLoggedIn() {
        if($this->session->has("AUTH_NAME") AND $this->session->has("AUTH_LOGIN") AND $this->session->has("AUTH_EMAIL")) {
            return true;
        }
        return false;
    }
}