<?php

class GroupMember extends Model
{
    /**
     * @var int
     */
    protected $group_id;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var \Datetime
     */
    protected $created_at;

    public static $belongs_to = array(
        array('members', 'class_name' => 'User'),
        array('group'),
    );

    public function before_create()
    {
        $find = self::find(array(
            'conditions' => array(
                'user_id=? AND group_id=?',
                $this->user_id,
                $this->group_id,
            ),
        ));

        if ($find) {
            return false;
        }
    }
}
