<?php

class IssueAlternative extends Model
{
	static $belongs_to = array(
		array('issue'),
		array('alternative')
	);

	static $validates_presence_of = array(
		array('issue_id'),
		array('alternative_id')
	);
}