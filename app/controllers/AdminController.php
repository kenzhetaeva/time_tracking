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
                $staffHours[] = UserController::getTodayUserStaff($user->id);
                $userArrivedTimes[] = $staffHours[$index][0][start_time];
                $index++;
            }

            $this->view->users = $users;
            $this->view->userArrivedTimes = $userArrivedTimes;
        }
        else if($this->isLoggedIn())
            return $this->response->redirect('/checkadmin');
    }

    public function staffMonthAction($id) {
        $month = date('m');
        $year = date('Y');

        $user = Users::findFirst($id);
        $array = UserController::getUserStaff($id, $month, $year);

        $staffHours = $array[0];
        $intervals = $array[1];
        $data['staffHours'] = $staffHours;

//        print_die($staffHours);
        $this->view->setVars([
            'user' => $user,
            'data' => $data,
            'intervals' => $intervals,
            'thisMonth' => $month,
            'thisYear' => $year,
        ]);
    }

    public function changeStaffHoursAction($id, $day, $month, $year) {
        $staffHours = UserController::getOneDayUserStaff($id, $day, $month, $year);
        $user = Users::findFirst($id);

        $data['staffHours'] = $staffHours;
        $this->view->setVars([
            'data' =>$data,
            'user' => $user,
            'day' => $day,
            'month' => $month,
            'year' => $year
        ]);
    }

    public function changeWorkHourAction() {
        $user = Users::findFirst();
        $workhour_start = $user->workhour_start;

        $this->view->setVars([
            'workhour_start' => $workhour_start
        ]);
    }

    public function editWorkHourAction() {
        if($this->request->isPost()) {
            $workhour_start = $this->request->getPost('workhour_start');

            $users = Users::find();

            if($users) {
                $hour = date('2000-01-01 '.$workhour_start);

                foreach ($users as $user) {
                    $user->workhour_start = $hour;

                    $user->update();
                }
                return $this->response->redirect('/admin/showlatecomers');
            }
        }
    }

    public function showLateComersAction() {

        $users = Users::find([
            'conditions' => 'is_active = 1'
        ]);

        $index = 0;
        $userArrivedTimes = [];
        $allStarts = [];

        foreach ($users as $user) {
            $query = new StaffHours;
            $staffHours = $query->getModelsManager()->createBuilder()
                ->columns(['*'])
                ->from(['staff1' => StaffHours::class])
                ->where('staff1.id = (select min(staff2.id) from StaffHours as staff2 where staff2.user_id = '.$user->id.' AND
                 DATE(staff2.start_time) = DATE(staff1.start_time) group by staff2.user_id)')
                ->andWhere('staff1.user_id = '.$user->id)
                ->getQuery()
                ->execute()
                ->toArray();
            $userArrivedTimes[] = $staffHours;
            $index++;
        }

        $this->view->setVars([
            'users' => $users,
            'userArrivedTimes' => $userArrivedTimes
        ]);
    }

    public function staffHoursEditAction() {
        if($this->request->isPost()) {
            $id = $this->request->getPost();
        }
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