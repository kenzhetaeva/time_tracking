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
                if(strcmp($password, $user->password) == 0) {
//                if ($this->security->checkHash($password, $user->password)) {

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

    public static function getUserStaff($userId, $month, $year)
    {
        $intervals = [];
        $monthDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for($i = 1; $i <= $monthDays; $i++) {
            $day = $year.'-'.$month.'-'.$i;
            $userStaff = StaffHours::find([
                'conditions' => 'user_id = :userId:
                                and DATE(start_time) = :day:',
                'bind' => [
                    'userId' => $userId,
                    'day' => $day,
                ]
            ])->toArray();

            $interval = 0;
            foreach ($userStaff as $uStaff) {
                if($uStaff['stop_time'] != NULL) {
                    $t1 = strtotime( $uStaff['start_time'] );
                    $t2 = strtotime( $uStaff['stop_time'] );
                    $diff = $t2 - $t1;
                    $interval += $diff;
                }
            }
            $intervals[] = $interval;
        }

        $thisMonth = $year.'-'.$month;
        $userStaff = StaffHours::find([
            'conditions' => 'user_id = :userId:
                            and start_time like :thisMonth:',
            'bind' => [
                'userId' => $userId,
                'thisMonth' => "%$thisMonth%",
            ]
        ])->toArray();

        $difference = 0;
        foreach ($userStaff as $uStaff) {
            if($uStaff['stop_time'] != NULL) {
                $t1 = strtotime( $uStaff['start_time'] );
                $t2 = strtotime( $uStaff['stop_time'] );
                $diff = $t2 - $t1;
                $difference += $diff;
            }
        }

        return [$userStaff, $intervals];
    }

    public static function getTodayUserStaff($userId)
    {
        $today = date('Y-m-d');
        $userStaff = StaffHours::find([
            'conditions' => 'user_id = :userId:
                            and start_time like :today:',
            'bind' => [
                'userId' => $userId,
                'today' => "%$today%",
            ]
        ])->toArray();


        return $userStaff;
    }

    public static function getOneDayUserStaff($userId, $day, $month, $year)
    {
        $thisDay = date($year.'-'.$month.'-'.$day);
        $userStaff = StaffHours::find([
            'conditions' => 'user_id = :userId:
                            and start_time like :day:',
            'bind' => [
                'userId' => $userId,
                'day' => "%$thisDay%",
            ]
        ])->toArray();


        return $userStaff;
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

            if($password1 != $password2) return $this->response->redirect('/changepassword');

            $user = Users::findFirst($userId);

            if($user) {
                $user->password = $password1;

                $user->update();
                return $this->response->redirect('/mainpage');
            }
        }
    }
}