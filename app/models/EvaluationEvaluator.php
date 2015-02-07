<?php

class EvaluationEvaluator extends Model
{
	static $belongs_to = array(
		array('evaluation'),
		array('evaluator', 'class_name' => 'User', 'primary_key' => 'id', 'foreign_key' => 'evaluator_id')
	);

	static $validates_presence_of = array(
		array('evaluation_id'),
		array('evaluator_id')
	);

	public function evaluator()
	{
		return $this->evaluator;
	}
}