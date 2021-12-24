<?php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Controller;

class TrackingController extends Controller
{
    public static function startAction(int $userId) {
        $response = [
            'success' => false
        ];

        if ($userId) {
            $startTime = new DateTime("now", new DateTimeZone('Asia/Bishkek'));
            $startTime = $startTime->format('Y-m-d H:i:s');

            $s_hour = new StaffHours();
            $s_hour->start_time = $startTime;
            $s_hour->stop_time = NULL;
            $s_hour->user_id = $userId;

            $s_hour->save();

            $response['success'] = true;
            $response['start_time'] = strftime("%H:%M:%S", strtotime($startTime));
        }

        return $response;
    }

    public static function stopAction(int $userId, $day, $month, $year) {
        $response = [
            'success' => false
        ];

        if ($userId) {

            $lastDate = StaffHours::findFirst([
                'conditions' => 'user_id = :userId:',
                'bind' => [
                    'userId' => $userId,
                ],
                'order' => 'start_time DESC'
            ]);

            $stopTime = new DateTime("now", new DateTimeZone('Asia/Bishkek'));
            $stopTime = $stopTime->format('Y-m-d H:i:s');

            $lastDate->stop_time = $stopTime;

            $lastDate->update();

            $array = Users::getUserStaff($userId, $month, $year);
            $intervals = $array[1];

            $response['success'] = true;
            $response['interval'] = gmdate("H:i:s", $intervals[$day - 1]);
            $response['stop_time'] = strftime("%H:%M:%S", strtotime($stopTime));
        }

        return $response;
    }
}