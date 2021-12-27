<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;

class Users extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $fullName;

    /**
     *
     * @var string
     */
    public $login;

    /**
     *
     * @var string
     */
    public $email;

    /**
     *
     * @var string
     */
    public $role;

    /**
     *
     * @var string
     */
    public $password;

    /**
     *
     * @var integer
     */
    public $is_active;

    /**
     *
     * @var string
     */
    public $workhour_start;

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'email',
            new EmailValidator(
                [
                    'model'   => $this,
                    'message' => 'Please enter a correct email address',
                ]
            )
        );

        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("time_tracking");
        $this->setSource("users");
    }

    public function onConstruct()
    {
        $this->workhour_start = new DateTime("now", new DateTimeZone('Asia/Bishkek'));
        $this->workhour_start = $this->workhour_start->format('2000-01-01 09:00:00');
        $this->is_active = true;
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Users[]|Users|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Users|\Phalcon\Mvc\Model\ResultInterface|\Phalcon\Mvc\ModelInterface|null
     */
    public static function findFirst($parameters = null): ?\Phalcon\Mvc\ModelInterface
    {
        return parent::findFirst($parameters);
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

        $choosedMonth = $year.'-'.$month;
        $userStaff = StaffHours::find([
            'conditions' => 'user_id = :userId:
                            and start_time like :choosedMonth:',
            'bind' => [
                'userId' => $userId,
                'choosedMonth' => "%$choosedMonth%",
            ]
        ])->toArray();

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
        $day = $year.'-'.$month.'-'.$day;
        $userStaff = StaffHours::find([
            'conditions' => 'user_id = :userId:
                                and DATE(start_time) = :day:',
            'bind' => [
                'userId' => $userId,
                'day' => $day,
            ]
        ]);

        return $userStaff;
    }
}
