<?php

use \Phalcon\Mvc\Model;
use Phalcon\Mvc\Controller;
use Phalcon\Security;

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
                if ($this->security->checkHash($password, $user->password)) {

                    $this->session->set('AUTH_ID', $user->id);
                    $this->session->set('AUTH_NAME', $user->fullName);
                    $this->session->set('AUTH_LOGIN', $user->login);
                    $this->session->set('AUTH_EMAIL', $user->email);

                    return $this->response->redirect('/mainpage');
                }
                else {
                    $error = "Incorrect password";
                    $this->view->error = $error;
                }
            } else {
                $error = "User not found";
                $this->view->error = $error;
            }
        }
        if($error == "")
            $this->view->error = $error;
    }

    public function logoutAction() {
        $this->session->destroy();
        return $this->response->redirect('/login');
    }

    public function changePasswordAction() {

    }

    public function changeUserPasswordAction() {
        if($this->request->isPost()) {
            $userId = $this->request->getPost('id');
            $password1 = $this->request->getPost('password1');
            $password2 = $this->request->getPost('password2');

            if (!$this->security->checkHash($password2, $this->security->hash($password1))) return $this->response->redirect('/changepassword');

            $user = Users::findFirst($userId);

            if($user) {
                $user->password = $this->security->hash($password1);

                $user->update();
                return $this->response->redirect('/mainpage');
            }
        }
    }
}