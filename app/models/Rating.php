<?php

class Rating extends Model
{
	static $has_many = array(
		array('user'),
		array('user_ratings')
	);

	static $validates_presence_of = array(
		array('name', 'message' => 'Classificação já cadastrada.')
	);

	static $validates_size_of = array(
		// array('name', 'within' => array(3, 25), 'too_short' => 'muito curto.', 'too_long' => 'muito grande.')
	);

	public function user_ratings()
	{
		return $this->user_ratings;
	}

	public function before_destroy()
	{
		$options = array(
            'conditions' => array(
                'rating_id' => $this->id)
        );

		UserRating::delete_all($options);
	}
}