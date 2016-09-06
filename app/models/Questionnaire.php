<?php

class Questionnaire extends Model
{
	private $id;
	private $name;
	private $name_private;
	private $created_at;
	private $updated_at;
	private $is_delete;
	private $user_id;

	static $has_many = array(
		array('questionnaire_issues'),
		array('issues', 
			'through' => 'questionnaire_issues', 
			'select' => 'issues.*, questionnaire_issues.order, questionnaire_issues.value', 
			'order' => 'questionnaire_issues.order'),

		array('evaluation_questionnaires'),
		array('evaluations', 'through' => 'evaluation_questionnaires')
	);

	static $belongs_to = array(
		array('user')
	);

	static $validates_presence_of = array(
		array('name'),
		array('user_id')
	);

	static $validates_size_of = array(
		array('name', 'within' => array(5, 255))
	);

	public function questionnaire_issues()
	{
		return $this->questionnaire_issues;
	}

	public function can_edit()
	{
		if ($this->evaluation_questionnaires) {
			foreach ($this->evaluation_questionnaires as $evaluation_questionnaire) {
				if ( ! $evaluation_questionnaire->evaluation->can_delete()) {
					return false;
				}
			}
		}

		return true;
	}

	public function getIssues()
	{
		return $this->issues;
	}

	public function name_private()
	{
		return $this->name_private;
	}

	public function before_destroy()
	{
		$evaluation_questionnaire = EvaluationQuestionnaire::find_by_questionnaire_id($this->id);

		if ($evaluation_questionnaire) {
			return false;
		}

		$options = array(
            'conditions' => array(
                'questionnaire_id' => $this->id)
        );

        EvaluationQuestionnaire::delete_all($options);
        QuestionnaireIssue::delete_all($options);
	}

	public function evaluationNames($separator = ', ')
	{
		$evaluations = $this->evaluations;

		if (empty($evaluations)) return null;

		$names = array();

		foreach ($evaluations as $evaluation) {
			array_push($names, $evaluation->name);
		}

		return implode($separator, $names);
	}

	public function copy(array $attributes)
	{
		$user_id = User::getUserLogger('id');

		$questionnaire = self::create($attributes);

		if ($questionnaire->is_invalid())
			return $questionnaire->errors->full_messages();

		$questionnaire_issues = $this->questionnaire_issues;

		foreach ($questionnaire_issues as $questionnaire_issue) {
			
			$copy_issue = QuestionnaireIssue::uniqueness(array(
				'issue_id' => $questionnaire_issue->issue_id,
				'questionnaire_id' => $questionnaire->id,
				'order' => $questionnaire_issue->order
			));

			if ($copy_issue->is_invalid())
				return $copy_issue->errors->full_messages();
		}

		return $questionnaire;		
	}
}