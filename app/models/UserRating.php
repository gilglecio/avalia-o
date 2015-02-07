<?php

class UserRating extends Model
{
	static $belongs_to = array(
		array('user'),
		array('rating')
	);

	static $validates_presence_of = array(
		array('user_id'),
		array('rating_id')
	);

	public function user()
	{
		return $this->user;
	}

	public function before_create()
	{
		$find = self::find(array(
			'conditions' => array(
				'user_id=? AND rating_id=?',
				$this->user_id,
				$this->rating_id
			)
		));		

		if ($find) {
			return false;
		}
	}
}