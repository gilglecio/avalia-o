<?php

class IssueAlternative extends Model
{
    /**
     * @var int
     */
    protected $issue_id;

    /**
     * @var int
     */
    protected $alternative_id;

    /**
     * @var int
     */
    protected $position;

    public static $belongs_to = array(
        array('issue'),
        array('alternative'),
    );

    public static $validates_presence_of = array(
        array('issue_id'),
        array('alternative_id'),
    );
}
