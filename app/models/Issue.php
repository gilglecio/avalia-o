<?php

class Issue extends Model
{
	static $has_many = array(
		array('issue_alternatives'),
		array('alternatives', 'through' => 'issue_alternatives'),
		array('questionnaire_issues'),
		array('questionnaires', 'through' => 'questionnaire_issues')
	);

	static $belongs_to = array(
		array('author', 'class_name' => 'User'),
		array('scale')
	);

	static $types = array(
		'open' => 'Aberta',
		'0-10' => '0 a 10',
		'scale' => 'Escala',
		'multiple_choice' => 'Alternativas (Múltipla)',
		'only_choice' => 'Alternativas (Única)',
		'boolean' => 'VERDADEIRO ou FALSO'
	);

	static $validates_presence_of = array(
		array('enunciation'),
		array('type'),
		array('user_id')
	);

	static $validates_size_of = array(
		array(
			'enunciation', 
			'within' => array(3, 1000),
			'too_long' => 'Enunciado muito grande. (Máx. 1000 caracteres).',
			'too_short' => 'Enunciado muito curto. (Min. 3 caracteres).'
		)
	);

	public function can_edit()
	{
		$questionnaire_issues = QuestionnaireIssue::find_all_by_issue_id($this->id);

		if ($questionnaire_issues) {
			return false;
		}

		return true;
	}

	public function getOptionsScale($option_id = null)
	{
		$options = ScaleOption::find_all_by_scale_id($this->scale->id);

		if ($option_id) {
			foreach ($options as $option) {
				if ($option->scale_row_id == $option_id) {
					return $option->row;
				}
			}
		}

		return $options;
	}

	public function questionnaire_issues()
	{
		return $this->questionnaire_issues;
	}

	public function scale()
	{
		return $this->scale;
	}

	static $validates_uniqueness_of = array(
		// array('enunciation')
	);

	static $before_save = array(
		'apply_justification'
	);

	public function apply_questionnaire($input)
	{
		if ( ! isset($input['questionnaire_id']))
			return false;

		$questionnaire_issue = QuestionnaireIssue::uniqueness(array(
			'issue_id' => $this->id,
			'questionnaire_id' => $input['questionnaire_id']
		));

		return $questionnaire_issue;
	}

	public function copy($questionnaire_id = null)
	{
		$attributes = array(
			'user_id' => $this->user_id,
			'type' => $this->type,

			'enunciation' => 'Cópia de: '.$this->enunciation,
			'required' => $this->required,

			'min_note' => $this->min_note,
			'max_note' => $this->max_note
		);

		if ($this->type != 'open') {
			$attributes['accepted_justification'] = $this->accepted_justification;
			$attributes['justification_required'] = $this->justification_required;
		}

		if ($this->type == 'multiple_choice') {
			$attributes['min_choice'] = $this->min_choice;
			$attributes['max_choice'] = $this->max_choice;
		}

		if ($this->type == 'scale') {
			$attributes['scale_id'] = $this->scale_id;
		}

		$copy = self::create($attributes);

		if ($questionnaire_id) {
			$questionnaire_issue = QuestionnaireIssue::create(array(
				'issue_id' => $copy->id,
				'questionnaire_id' => $questionnaire_id
			));
		}

		if (in_array($this->type, array('multiple_choice', 'only_choice'))) {
			$this->copy_alternatives($copy);
		}

		return $copy;
	}

	public function copy_alternatives($issue)
	{
		foreach ($this->issue_alternatives as $issue_alternative) {
			IssueAlternative::create(array(
				'alternative_id' => $issue_alternative->alternative_id,
				'issue_id' => $issue->id,
				'position' => $issue_alternative->position
			));
		}
	}

	public function alternatives_for_textarea()
	{
		$names = array();

		foreach ($this->alternatives as $alternative) {
			array_push($names, $alternative->name);
		}

		return implode("\n", $names);
	}

	public static function getAttributes($type, $post, $edit = false)
	{
		$attributes = array(
			'enunciation' => isset($post['enunciation']) ? $post['enunciation'] : '',
			'required' => isset($post['required']) ? 1 : 0,
			'min_note' => isset($post['min_note']) ? $post['min_note'] : 1,
			'max_note' => isset($post['max_note']) ? $post['max_note'] : 10
		);

		if ( ! $edit) {
			$attributes['user_id'] = User::getUserLogger('id');
			$attributes['type'] = isset($post['type']) ? $post['type'] : '';
		}

		if ($type != 'open') {
			$attributes['accepted_justification'] = isset($post['accepted_justification']) ? 1 : 0;
			$attributes['justification_required'] = isset($post['justification_required']) ? 1 : 0;
		}

		if ($type == 'multiple_choice') {
			$attributes['min_choice'] = isset($post['min_choice']) ? $post['min_choice'] : null;
			$attributes['max_choice'] = isset($post['max_choice']) ? $post['max_choice'] : null;
		}

		return $attributes;
	}

	public function selectScale($scale_id)
	{
		$update = $this->update_attributes(array(
			'scale_id' => $scale_id
		));

		return $update;
	}

	public function newScale(array $input)
	{
		foreach ($input['label'] as $key => $label)
			if ($label) $input['label'][$key] = ucwords(trim(strip_tags($label)));

		foreach ($input['value'] as $key => $value) {
			$value = $value ? $value : $key+1;
			$input['value'][$key] = (int) $value;
		}

		$input['rows'] = array_filter(array_combine($input['value'], $input['label']));

		if (empty($input['rows']))
			return false;

		$scale = Scale::uniqueness(array(
			'name' => $input['name']
		));

		if ( ! $scale)
			return false;

		foreach ($input['rows'] as $position => $name) {

			$scale_row = ScaleRow::uniqueness(array(
				'name' => $name,
				'position' => $position
			));

			if ( ! $scale_row)
				return false;

			$scale_options = ScaleOption::uniqueness(array(
				'scale_id' => $scale->id,
				'scale_row_id' => $scale_row->id
			));

			if ($scale_options->is_invalid())
				return $scale_options->errors->full_messages();
		}

		return $scale->id;
	}

	public function editScales($input)
	{
		$action = isset($input['action']) ? $input['action'] : 'new';
		$scale_id = isset($input['scale_id']) ? $input['scale_id'] : false;

		switch ($action) {
			case 'new':
				$scale_id = $this->newScale(array(
					'name' => $input['scale_name'],
					'label' => $input['scale_label'],
					'value' => $input['scale_value'],
					'issue_id' => $this->id
				));

				$update = $this->update_attributes(array(
					'scale_id' => $scale_id
				));

				break;
			case 'select':
				if ( ! $scale_id) {
					return false;
				}
				$update = $this->selectScale($scale_id, $this->id);
				break;
		}

		return $update;
	}

	public function apply_alternatives($alternative_id)
	{
		$issue_alternative = IssueAlternative::create(array(
			'issue_id' => $this->id,
			'alternative_id' => $alternative_id
		));

		return $issue_alternative->is_valid();
	}

	public function editAlternatives($alternative_string)
	{
		$split = explode("\n", $alternative_string);

		$this->destroy_alternatives();

		foreach ($split as $alternative) {

			$alternative = trim(strip_tags($alternative));

			$create_alternative = Alternative::uniqueness(array(
				'name' => $alternative
			));

			$this->apply_alternatives($create_alternative->id);
		}

		return true;
	}

	public function questionnaire()
	{
		return $this->questionnaire;
	}

	public function self_types()
	{
		return self::$types;
	}

	public function getType()
	{
		return self::$types[$this->type];
	}

	public function getRequired()
	{
		return $this->required ? 'Sim' : '-';
	}

	public function getJustification()
	{
		return $this->accepted_justification ? ($this->justification_required ? 'Obrigatório' : 'Opcional') : '-';
	}

	public function getAlternativeString()
	{
		if (in_array($this->type, array('only_choice', 'multiple_choice'))) {

			$names = array();

			foreach ($this->alternatives as $alternative) {
				array_push($names, $alternative->name);
			}

			return implode(', ', $names);

		} else if ($this->type == 'scale') {

			$names = array();

			foreach ($this->scale as $scale) {
				array_push($names, $scale->name);
			}
			
			return implode(', ', $names);
		}
	}

	public function getAlternatives()
	{
		return $this->alternatives;
	}

	public function issue_scales()
	{
		return $this->issue_scales;
	}

	public function destroy_alternatives()
	{
        IssueAlternative::delete_all(array(
            'conditions' => array(
                'issue_id' => $this->id)
        ));
	}

	public function destroy_questionnaires()
	{
		QuestionnaireIssue::delete_all(array(
            'conditions' => array(
                'issue_id' => $this->id)
        ));
	}

	public function before_destroy()
	{
		$this->destroy_alternatives();
		$this->destroy_questionnaires();

		$answers = Answer::find_by_issue_id($this->id);

		if ($answers) {
			return false;
		}			
	}

	public function apply_justification()
	{
		if ($this->justification_required) {
			$this->accepted_justification = 1;
			$this->required = 1;
		}
	}
}