<?php

class IssueAlternative extends Model
{
	private $issue_id;
	private $alternative_id;
	private $position;

	static $belongs_to = array(
		array('issue'),
		array('alternative')
	);

	static $validates_presence_of = array(
		array('issue_id'),
		array('alternative_id')
	);
}