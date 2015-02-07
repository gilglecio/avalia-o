<?php

class EvaluationQuestionnaire extends Model
{
	static $belongs_to = array(
		array('evaluation'),
		array('questionnaire')
	);

	static $validates_presence_of = array(
		array('evaluation_id'),
		array('questionnaire_id')
	);

	public function questionnaire()
	{
		return $this->questionnaire;
	}
}