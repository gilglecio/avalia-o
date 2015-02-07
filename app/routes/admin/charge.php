<?php

$app->group('/charge', function () use ($app) {

	$app->post('/new', function () use ($app) {

		$post = $app->request->post();
		$names = isset($post['name']) ? strip_tags(trim($post['name'])) : '';
		$split = array_filter(explode(',', $names));

		$charges = array();
		$errors = array();

		foreach ($split as $name) {
			
			$name = trim($name);

			$charge = Charge::create(array(
				'name' => $name
			));

			if ($charge->is_invalid()) {
				array_push($errors, $name . ' já estava cadastrado.');
			} else {
				array_push($charges, array(
					'id' => $charge->id,
					'name' => $charge->name
				));
			}		
		}

		$json['charges'] = $charges;
		$json['errors'] = null;

		if (! empty($errors)) {
			$json['errors'] = $errors;
		}

		die(json_encode($json));
	});
});