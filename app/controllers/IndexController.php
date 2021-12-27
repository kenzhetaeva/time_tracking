<?php
declare(strict_types=1);

//use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;

class IndexController extends ControllerBase
{

    // checks is authorized, if not redirects to login page, otherwise - to main page
    public function indexAction()
    {
        $this->authorized();
        return $this->response->redirect('/mainpage');

    }

    // user's profile page - tracking his personal staff hours
    public function mainPageAction() {
        $this->authorized();
        $month = date('m');
        $year = date('Y');
        if ($this->request->get('month')) {
            $month = $this->request->get('month');
        }
        if ($this->request->get('year')) {
            $year = $this->request->get('year');
        }

        $array = Users::getUserStaff($this->session->get('AUTH_ID'), $month, $year);

        $staffHours = $array[0];
        $intervals = $array[1];

        $holidays = Holidays::find();
        $holidayDays = [];
        foreach ($holidays as $holiday) {
            $holidayDays[] = strtotime($holiday->holiday_day);
        }

        $monthDays = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
        $firstDay = $year.'-'.$month.'-1';
        $lastDay = $year.'-'.$month.'-'.$monthDays;

        $monthStaffHours = 8 * Holidays::getWorkingDays($firstDay, $lastDay, $holidayDays);
        $doneMonthStaff = number_format((float)((array_sum($intervals)) / 3600), 2, '.', '');

        $data['stopButtonActive'] = false;
        if (is_null($staffHours[count($staffHours)-1]['stop_time'])) {
            $data['stopButtonActive'] = true;
        }
        $data['staffHours'] = $staffHours;

        $this->view->setVars([
            'data' => $data,
            'holidays' => $holidayDays,
            'doneMonthStaff' => $doneMonthStaff,
            'monthStaffHours' => $monthStaffHours,
            'intervals' => $intervals,
            'thisMonth' => $month,
            'thisYear' => $year,
        ]);
    }

    // if start button pressed
    public function startAction()
    {
        if ($this->request->isPost()) {
            $userId = $this->request->getPost('userId');
            $response = TrackingController::startAction((int)$userId);

            if ($response['success']) {
                exit(json_encode($response));
            }

        }
    }

    // if stop button pressed
    public function stopAction()
    {
        if ($this->request->isPost()) {
            $userId = $this->request->getPost('userId');
            $day = $this->request->getPost('day');
            $month = $this->request->getPost('month');
            $year = $this->request->getPost('year');

            $response = TrackingController::stopAction((int)$userId, $day, $month, $year);

            if ($response['success']) {
                exit(json_encode($response));
            }
        }
    }

    // redirects to view - if user is admin
    public function checkAdminAction() {

    }

    // shows error 404
    public function show404Action() {

    }

    // shows error 503
    public function show503Action() {

    }

}

