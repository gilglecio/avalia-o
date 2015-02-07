<?php

class QuestionnaireIssue extends Model
{

	static $belongs_to = array(
		array('questionnaire'),
		array('issue')
	);

	static $validates_presence_of = array(
		array('questionnaire_id'),
		array('issue_id')
	);

	// public function before_destroy()
	// {
	// 	$evaluation_questionnaire = EvaluationQuestionnaire::find_by_questionnaire_id($this->questionnaire_id);

	// 	if ($evaluation_questionnaire) {
	// 		return false;
	// 	}
	// }

	public function issue()
	{
		return $this->issue;
	}

	public static function uniqueness(array $attributes)
	{
		$find = self::find(array(
			'conditions' => array(
				'issue_id=? AND questionnaire_id=?', 
				$attributes['issue_id'], 
				$attributes['questionnaire_id']
			)
		));

		if ($find) {
			return $find;
		}

		if ( ! isset($attributes['order']))
			$attributes['order'] = 10;

		$create = self::create($attributes);

		return $create;
	}
}