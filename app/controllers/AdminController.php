<?php

use \Phalcon\Mvc\Model;
use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;
use Phalcon\Security;

class AdminController extends ControllerBase
{
    // shows list of users and their arrived times for today
    public function dashboardAction() {

        $this->authorized();

        $userRole = $this->session->get('AUTH_ROLE');

        if(strcmp($userRole, 'admin') == 0 ) {
            $users = Users::find([
                'conditions' => 'is_active = 1'
            ]);

            $index = 0;
            $userArrivedTimes =[];
            foreach ($users as $user) {
                $staffHours[] = Users::getTodayUserStaff($user->id);
                $userArrivedTimes[] = $staffHours[$index][0][start_time];
                $index++;
            }

            $this->view->users = $users;
            $this->view->userArrivedTimes = $userArrivedTimes;
        }
        else if($this->isLoggedIn()) {
            return $this->response->redirect('/checkadmin');
        }
    }

    // shows all start/stops(for month) for single user
    public function staffMonthAction($id) {
        $month = date('m');
        $year = date('Y');

        $user = Users::findFirst($id);
        $array = Users::getUserStaff($id, $month, $year);

        $staffHours = $array[0];
        $intervals = $array[1];
        $data['staffHours'] = $staffHours;

        $this->view->setVars([
            'user' => $user,
            'data' => $data,
            'intervals' => $intervals,
            'currentMonth' => $month,
            'currentYear' => $year,
        ]);
    }

    // redirects to form to change user's staff hours for one day
    public function changeStaffHoursAction($id, $day, $month, $year) {
        $staffHours = (Users::getOneDayUserStaff($id, $day, $month, $year))->toArray();
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

    // changes user's start/stops for one day
    public function staffHoursEditAction() {
        if($this->request->isPost()) {
            $id = $this->request->getPost('id');
            $day = $this->request->getPost('day');
            $month = $this->request->getPost('month');
            $year = $this->request->getPost('year');
            $amount = $this->request->getPost('amount');
            $allStarts = [];
            $allStops = [];

            for($i = 0; $i < $amount; $i++) {
                $allStarts[] = $year.'-'.$month.'-'.$day.' '.$this->request->getPost('start_'.$i).':00';
                $allStops[] = $year.'-'.$month.'-'.$day.' '.$this->request->getPost('stop_'.$i).':00';
            }

            $user = Users::findFirst($id);

            if($user) {
                $staffHours = Users::getOneDayUserStaff($id, $day, $month, $year);

                $i = 0;
                foreach ($staffHours as $staffHours) {
                    $staffHours->start_time = $allStarts[$i];
                    $staffHours->stop_time = $allStops[$i];
                    $staffHours->update();

                    $i++;
                }

                return $this->response->redirect('/admin/staffmonth/'.$id);
            }
        }
    }

    // redirects to form to add holiday day
    public function addHolidaysAction() {
    }

    // adds new holiday day
    public function editHolidaysAction() {
        if($this->request->isPost()) {
            $holiday = $this->request->getPost('holiday');
            $isRepeated = $this->request->getPost('isRepeated');
            $day = date($holiday.' 00:00:00');

            if($isRepeated == 'on') {
                for($i = 0; $i < 10; $i++) {
                    $futureDate=date('Y-m-d H:i:s', strtotime('+'.$i.' year', strtotime($day)) );

                    $newHoliday = new Holidays();
                    $newHoliday->holiday_day = $futureDate;

                    $newHoliday->save();
                }
            }

            return $this->response->redirect('/admin');
        }
    }

    // redirects to form to change beginning of working day
    public function changeWorkHourAction() {
        $user = Users::findFirst();
        $workhourStart = $user->workhour_start;

        $this->view->setVars([
            'workhour_start' => $workhourStart
        ]);
    }

    // changes beginning of working day
    public function editWorkHourAction() {
        if($this->request->isPost()) {
            $workhourStart = $this->request->getPost('workhour_start');

            $users = Users::find();

            if($users) {
                $hour = date('2000-01-01 '.$workhourStart);

                foreach ($users as $user) {
                    $user->workhour_start = $hour;

                    $user->update();
                }
                return $this->response->redirect('/admin/showlatecomers');
            }
        }
    }

    // shows latecomers for all time
    public function showLateComersAction() {

        $users = Users::find([
            'conditions' => 'is_active = 1'
        ]);

        $index = 0;
        $userArrivedTimes = [];

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

    // deletes user (sets user as inactive)
    public function userDeleteAction($id) {

        $user = Users::findFirst($id);

        if($user) {
            $user->is_active = 0;
            $user->role = 'guest';
            $user->update();
            return $this->response->redirect('/admin');
        }
    }


    // redirect to form to edit user information(login, fullName, email, password)
    public function userEditAction($id) {

        $user = Users::findFirst($id);

        if ($user) {
            $this->view->user = $user;
        }
    }

    // updates information about one user
    /**
     * @property Request $request
     */
    public function userUpdateAction() {

        if ($this->request->isPost()) {

            $id = $this->request->getPost('id');

            $user = Users::findFirst($id);

            if($user) {

                $fullName = $this->request->getPost('fullName');
                $login = $this->request->getPost('login');
                $email = $this->request->getPost('email');
                $password = $this->request->getPost('password');

                $user->fullName = $fullName;
                $user->login = $login;
                $user->email = $email;
                $user->password = $this->security->hash($password);

                $user->update();
                return $this->response->redirect('/admin');
            }
        }
    }

    // redirects to form to add new user
    public function userAddAction() {

    }

    // adds new user
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
            $user->password = $this->security->hash($password);
            $user->is_active = 1;
            $user->role = 'user';

            $user->save();
            return $this->response->redirect('/admin');
        }
    }
}