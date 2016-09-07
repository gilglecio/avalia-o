<?php

class UserRating extends Model
{
    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var int
     */
    protected $rating_id;

    public static $belongs_to = array(
        array('user'),
        array('rating'),
    );

    public static $validates_presence_of = array(
        array('user_id'),
        array('rating_id'),
    );

    public function user()
    {
        return $this->user;
    }

    public function before_create()
    {
        $find = self::find(array(
            'conditions' => array(
                'user_id=? AND rating_id=?',
                $this->user_id,
                $this->rating_id,
            ),
        ));

        if ($find) {
            return false;
        }
    }
}
