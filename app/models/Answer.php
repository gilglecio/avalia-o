<?php

class Answer extends Model
{
	private $id;
	private $evaluation_sending_id;
	private $issue_id;
	private $valued_id;
	private $answer;
	private $justification;
	private $created_at;
	private $updated_at;
	private $status;

	static $has_many = array(
		array('corrections')
	);

	static $belongs_to = array(
		array('issue'),
		array('valued', 'class_name' => 'User', 'primary_key' => 'id', 'foreign_key' => 'valued_id'),
		array('evaluation_sending')
	);

	static $validates_presence_of = array(
		array('issue_id'),
		array('valued_id'),
		array('answer')
	);

	public function getAnswer()
	{
		switch ($this->issue->type) {
			case 'multiple_choice':

				$alternatives = $this->issue->alternatives;
				$answer = json_decode($this->answer);
				$answer = array_flip($answer);

				foreach ($alternatives as $alternative) {

					if (isset($answer[$alternative->id]))
						$answer[$alternative->id] = $alternative->name;
				}

				return '- '.implode(';<br />- ', array_values($answer)).'.';

				break;

			case 'only_choice':
				
				$alternatives = $this->issue->alternatives;

				foreach ($alternatives as $alternative) {
					if ($alternative->id == $this->answer) {
						return '- '.$alternative->name.'.';
					}
				}

				break;

			case '0-10':
				return number_format($this->answer, 2, ',', '.');
				break;

			case 'scale':

				$scale_row = $this->issue->getOptionsScale($this->answer);
				return $scale_row->name;

			case 'boolean':
				$boolean = array(
					'Falsa',
					'Verdadeira'
				);
				return $boolean[$this->answer];
			
			default:
				
				return $this->answer;

				break;
		}
	}

	public function sending()
	{
		$sendings = Sending::all(array(
			'conditions' => array(
				'valued_id' => $this->valued_id
			)
		));

		foreach ($sendings as $key => $sending) {
			if ($sending->evaluation_sending->evaluation_id != $this->evaluation_id) {
				unset($sendings[$key]);
			}
		}

		if ( ! is_array($sendings) OR empty($sendings)) {
			return array();
		}

		$sendings = array_values($sendings);

		return $sendings[0];
	}

	public function valued()
	{
		return $this->valued;
	}

	public function issue()
	{
		return $this->issue;
	}

	public function getMembers()
	{
		return $this->members;
	}

	public static function name_valueds($evaluation_sending_id)
	{
		$answers = self::all(array(
			'conditions' => array(
				'evaluation_sending_id =?', $evaluation_sending_id
			),
			'group' => 'valued_id'
		));
		$names = array();

		foreach ($answers as $answer) {
			array_push($names, $answer->valued->name);
		}

		return $names;
	}

	public static function uniqueness($issues, $valued_id, $evaluation_sending_id)
	{
		if (empty($issues))
			return false;

		foreach ($issues as $issue_id => $issue) {
			
			$find = self::find(array(
				'conditions' => array('issue_id=? AND valued_id=? AND evaluation_sending_id=?',
					$issue_id, $valued_id, $evaluation_sending_id)
			));

			if ($find) continue;

			$attributes = array(
				'issue_id' => $issue_id,
				'valued_id' => $valued_id,
				'evaluation_sending_id' => $evaluation_sending_id,
				'answer' => (isset($issue['answer']) ? $issue['answer'] : '')
			);

			if (isset($issue['justification']))
				$attributes['justification'] = $issue['justification'];

			$answer = self::create($attributes);

			if ($answer->is_invalid())
				return $answer->errors->full_messages();
		}

		return true;
	}

	public static function prepare($post)
	{
		if (empty($post))
			return false;

		$issues = array();

		foreach ($post as $key => $value) {

			$split = explode('-', $key);

			if (isset($split[1])) {
				if ($split[0].$split[1] == '010') {

					$split[0] = $split[0].'-'.$split[1];
					$split[1] = $split[2];
					$split[2] = $split[3];
				}
			}
			
			if (is_array($value))
				$value = json_encode($value);

			if (in_array($split[0], array_keys(Issue::$types))) {
				$issues[$split[1]][$split[2]] = $value; 
			}
		}
		
		return $issues;
	}

	public function before_destroy()
	{
		$options = array(
            'conditions' => array(
                'answer_id' => $this->id)
        );
        	
		foreach (Correction::all($options) as $correction) {
			$correction->delete();
		}
	}
}