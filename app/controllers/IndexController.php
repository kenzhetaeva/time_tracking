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
        $staffHours = UserController::getUserStaff($this->session->get('AUTH_ID'));

        $data['stopButtonActive'] = false;
        if (is_null($staffHours[count($staffHours)-1]['stop_time'])) {
            $data['stopButtonActive'] = true;
        }
        $data['staffHours'] = $staffHours;

        $this->view->setVars([
            'data' => $data
        ]);
    }

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

    public function stopAction()
    {
        if ($this->request->isPost()) {
            $userId = $this->request->getPost('userId');

            $response = TrackingController::stopAction((int)$userId);

            if ($response['success']) {
                exit(json_encode($response));
            }
        }
    }

    public function checkAdminAction() {

    }

    public function show404Action() {

    }

    public function show503Action() {

    }

}

