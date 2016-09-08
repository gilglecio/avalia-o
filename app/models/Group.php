<?php

class Group extends Model
{
    public static $has_many = array(
        array('group_members'),
        array('members', 'through' => 'group_members', 'class_name' => 'User'),
    );

    public static $belongs_to = array(
        array('author', 'class_name' => 'User'),
    );

    public static $validates_uniqueness_of = array(
        array('name', 'message' => 'já existe.'),
    );

    public static $validates_size_of = array(
        array('name',
            'within' => array(5, 45),
            'too_short' => 'muito pequeno.',
            'too_long' => 'muito grande, até 45 caraceres.', ),
    );

    public function getMembers()
    {
        return $this->members;
    }

    public function before_destroy()
    {
        $options = array(
            'conditions' => array(
                'group_id' => $this->id, ),
        );

        GroupMember::delete_all($options);
        EvaluationGroup::delete_all($options);
    }
}
