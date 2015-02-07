<?php

class SendingBcc extends Model
{
	static $table_name = 'sending_bcc';

	static $belongs_to = array(
		array('evaluation_sending')
	);

	static $validates_uniqueness_of = array(
		array('token')
	);

	static $validates_presence_of = array(
		array('evaluation_sending_id'),
		array('name'),
		array('email')
	);

	public function sending()
	{
		return $this->sending;
	}

	public function getAnswer()
	{
		$answers = Answer::find_all_by_evaluation_sending_id($this->evaluation_sending_id);
		return $answers;
	}

	public function getEncodeToken()
	{
		return Sending::encodeToken($this->token);
	}

	public function before_create()
	{
		$this->token = crypt($this->evaluation_sending_id);
	}

	public static function uniqueness($attributes)
	{
		$find = self::find(array(
			'conditions' => array(
				'evaluation_sending_id=? AND email =? ',
				$attributes['evaluation_sending_id'],
				$attributes['email']
			)
		));

		if ($find) {
			return $find;
		}			

		$create = self::create($attributes);

		return $create;
	}
}