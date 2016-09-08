<?php

class IssueAlternative extends Model
{
    public static $belongs_to = array(
        array('issue'),
        array('alternative'),
    );

    public static $validates_presence_of = array(
        array('issue_id'),
        array('alternative_id'),
    );
}
