<?php

use \Phalcon\Mvc\Model;
use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;
use Phalcon\Security;

class AdminController extends ControllerBase
{
    public function dashboardAction() {

        $this->authorized();

        $userLogin = $this->session->get('AUTH_LOGIN');

        if(strcmp($userLogin, 'admin') == 0 ) {

            $users = Users::find([
                'conditions' => 'is_active = 1'
            ]);

            $index = 0;
            $userArrivedTimes =[];
            foreach ($users as $user) {
                $staffHours[] = UserController::getUserStaff($user->id);
                $userArrivedTimes[] = $staffHours[$index][0][start_time];
                $index++;
            }

            $this->view->users = $users;
            $this->view->userArrivedTimes = $userArrivedTimes;
        }
        else if($this->isLoggedIn())
            return $this->response->redirect('/checkadmin');
    }

    public function userDeleteAction($id) {

        $user = Users::findFirst($id);

        if($user) {
            $user->is_active = 0;
            $user->update();
            return $this->response->redirect('/admin');
        }
    }


    public function userEditAction($id) {

        $user = Users::findFirst($id);

        if ($user) {
            $this->view->user = $user;
        }
    }

    /**
     * @property Request $request
     */
    public function userUpdateAction() {

        if ($this->request->isPost()) {

            $id = $this->request->getPost('id');

//            var_dump($id);
//            die();

            $user = Users::findFirst($id);

            if($user) {

                $fullName = $this->request->getPost('fullName');
                $login = $this->request->getPost('login');
                $email = $this->request->getPost('email');
                $password = $this->request->getPost('password');

                $user->fullName = $fullName;
                $user->login = $login;
                $user->email = $email;
                $user->password = $password;

                $user->update();
                return $this->response->redirect('/admin');
            }
        }
    }

    public function userAddAction() {

    }

    /**
     * @property Request $request
     */
    public function userNewAction() {

        if ($this->request->isPost()) {

            $fullName = $this->request->getPost('fullName');
            $login = $this->request->getPost('login');
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            $user = new Users();
            $user->fullName = $fullName;
            $user->login = $login;
            $user->email = $email;
            $user->password = $password;
            $user->is_active = 1;

            $user->save();
            return $this->response->redirect('/admin');
        }
    }
}