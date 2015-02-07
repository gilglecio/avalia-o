<?php

$app->group('/rating', function () use ($app) {

	$app->post('/new', function () use ($app) {

		$post = $app->request->post();
		$names = isset($post['name']) ? strip_tags(trim($post['name'])) : '';
		$split = array_filter(explode(',', $names));

		$ratings = array();
		$errors = array();

		foreach ($split as $name) {
			
			$name = trim($name);

			$rating = Rating::create(array(
				'name' => $name
			));

			if ($rating->is_invalid()) {
				array_push($errors, $name . ' já estava cadastrado.');
			} else {
				array_push($ratings, array(
					'id' => $rating->id,
					'name' => $rating->name
				));
			}		
		}

		$json['ratings'] = $ratings;
		$json['errors'] = null;

		if (! empty($errors)) {
			$json['errors'] = $errors;
		}

		die(json_encode($json));
	});
});