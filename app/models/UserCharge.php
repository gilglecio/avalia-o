<?php

class UserCharge extends Model
{
    public static $belongs_to = array(
        array('user'),
        array('charge'),
    );

    public static $validates_presence_of = array(
        array('user_id'),
        array('charge_id'),
    );

    public function user()
    {
        return $this->user;
    }

    public function before_create()
    {
        $find = self::find(array(
            'conditions' => array(
                'user_id=? AND charge_id=?',
                $this->user_id,
                $this->charge_id,
            ),
        ));

        if ($find) {
            return false;
        }
    }
}
