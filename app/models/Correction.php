<?php

class Correction extends Model
{
	private $id;
	private $answer_id;
	private $evaluator_id;
	private $note;
	private $justification;
	private $created_at;
	private $updated_at;

	static $belongs_to = array(
		array('answer'),
		array('evaluator', 'class_name' => 'User', 'primary_key' => 'id', 'foreign_key' => 'evaluator_id')
	);

	static $validates_presence_of = array(
		array('answer_id'),
		array('evaluator_id'),
		array('note')
	);

	public function answer()
	{
		return $this->answer;
	}

	public function getNote()
	{
		return number_format($this->note, 2, ',', '.');
	}

	public static function bySending($sending_id)
	{
		$sending = Sending::find_by_id($sending_id);

		if ( ! $sending)
			return array();

		$evaluation_sending = $sending->evaluation_sending;
		
		$answers = Answer::find_all_by_evaluation_sending_id_and_valued_id($evaluation_sending->id, $sending->valued_id);

		$data = array();

		foreach ($answers as $answer) {
			
			$answer_corrections = $answer->corrections;

			$a['issue'] = $answer->issue;
			$a['answer'] = $answer;

			foreach ($answer_corrections as $correction) {

				$c['note'] = $correction->getNote();
				$c['justification'] = $correction->justification;

				$a['evaluators'][$correction->evaluator_id]['evaluator'] = $correction->evaluator;
				$a['evaluators'][$correction->evaluator_id]['correction'] = $c;

				$data['evaluators'][$correction->evaluator_id] = $correction->evaluator;

				if ( ! isset($data['sum'][$correction->evaluator_id]))
					$data['sum'][$correction->evaluator_id] = 0;

				$data['sum'][$correction->evaluator_id] += $correction->note;

				$data['media'][$correction->evaluator_id] = $data['sum'][$correction->evaluator_id] / count($answers);
			}

			$data['answers'][$answer->id] = $a;
		}

		if ( ! isset($data['evaluators'])) {
			$data['evaluators'] = array();
		}

		if ( ! isset($data['media'])) {
			$data['media'] = array();
		}

		return $data;
	}

	public static function getByValuedId($valued_id)
	{
		$corrections = Correction::all();

		$data = array();

		if ($corrections) {
			foreach ($corrections as $correction) {
				
				if ($correction->answer->valued_id == $valued_id) {
					array_push($data, $correction);
				}

			}
		}

		return $data;
	}

	public static function uniqueness($corrections, $evaluator_id)
	{
		if (empty($corrections))
			return false;

		foreach ($corrections as $answer_id => $column) {
			
			$find = self::find(array(
				'conditions' => array('answer_id=? AND evaluator_id=?',
					$answer_id,
					$evaluator_id
				)
			));

			if ($find) {
				//dd($find);
				continue;
			}

			$column['note'] = isset($column['note']) ? $column['note'] : 0;

			$attributes = array(
				'answer_id' => $answer_id,
				'evaluator_id' => $evaluator_id,
				'note' => $column['note'],
				'justification' => $column['justification']
			);

			$correction = self::create($attributes);

			if ($correction->is_invalid())
				return $correction->errors->full_messages();
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

			if ($split[0] == 'correct') {
				
				if ($split[2] == 'note') {
					$issues[$split[1]]['note'] = $value;
					continue;
				}

				$issues[$split[1]]['justification'] = $value;
			}
		}

		return $issues;
	}
}