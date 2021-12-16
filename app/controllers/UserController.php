<?php

use \Phalcon\Mvc\Model;
use Phalcon\Mvc\Controller;
use Phalcon\Security;

//use Phalcon\Escaper;
use Phalcon\Flash\Session as FlashSession;
//use Phalcon\Session\Adapter\Stream;
//use Phalcon\Session\Manager;

class UserController extends Controller
{
    /**
     * @property Request  $request
     * @property Security $security
     */
    public function loginAction() {

        $error = "";

        if ($this->request->isPost()) {
            $login = $this->request->getPost('login');
            $password = $this->request->getPost('password');

            $user = Users::findFirst([
                'conditions' => "login = :login: and is_active = 1",
                'bind' => [
                    'login' => $login
                ]
            ]);

            if ($user) {
                if(strcmp($password, $user->password) == 0) {
//                if ($this->security->checkHash($password, $user->password)) {

                    $this->session->set('AUTH_NAME', $user->fullName);
                    $this->session->set('AUTH_LOGIN', $user->login);
                    $this->session->set('AUTH_EMAIL', $user->email);

                    return $this->response->redirect('/mainpage');
//                    echo $this->view->render('user/tracking_page');
//                    var_dump(1);
//                    die();
                }
                else {
                    $error = "Incorrect password";
                    $this->view->error = $error;
//                    return $this->response->redirect('/login');
                }
            } else {
                $error = "User not found";
                $this->view->error = $error;
//                return $this->response->redirect('/login');
            }
        }
        if($error == "")
            $this->view->error = $error;
    }

    public function logoutAction() {
        $this->session->destroy();
        return $this->response->redirect('/login');
    }
}