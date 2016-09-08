<?php

class EvaluationGroup extends Model
{
    public static $belongs_to = array(
        array('evaluation'),
        array('group'),
    );

    public static $validates_presence_of = array(
        array('evaluation_id'),
        array('group_id'),
    );

    public function group()
    {
        return $this->group;
    }
}
