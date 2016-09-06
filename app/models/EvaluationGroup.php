<?php

class EvaluationGroup extends Model
{
	private $evaluation_id;
	private $group_id;

	static $belongs_to = array(
		array('evaluation'),
		array('group')
	);

	static $validates_presence_of = array(
		array('evaluation_id'),
		array('group_id')
	);

	public function group()
	{
		return $this->group;
	}
}