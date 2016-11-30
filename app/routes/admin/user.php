<?php 

$app->group('/user', function () use ($app) {

	$app->get('(/:profile_type)', function ($profile_type = null) use ($app)
	{
		$options = array(
			'include' => array('ratings', 'charges'),
			'order' => 'name ASC'
		);

		$users = $profile_type ? User::find_all_by_profile_type($profile_type, $options) : User::all($options);

		$app->render('admin/user/list.html.twig', array(
			'title' => 'Lista',
			'users' => $users,
			'profile_current' => $profile_type
		));

	})->name('user_list');

	$app->group('/valued', function () use ($app)
	{
		$app->group('/rating', function () use ($app)
		{
			$app->get('/', function () use ($app)
			{
				$ratings = Rating::all(array(
					'order' => 'id DESC'
				));

				$app->render('admin/user/rating/list.html.twig', array(
					'title' => 'Classificações',
					'ratings' => $ratings
				));

			})->name('valued_rating_list');

			$app->post('/new', function () use ($app)
			{
				$post = $app->request->post();

				split_ratings($post['name']);
				$app->redirect($app->request->getReferrer());

			})->name('valued_rating_new');

			$app->group('/:id', function ($id = null) use ($app)
			{
				$app->get('/', function ($id) use ($app)
				{
					$app->redirect($app->request->getReferrer());
				});

				$app->get('/delete', function ($id) use ($app)
				{
					$find = Rating::find_by_id($id);
					$find->delete();
					$app->redirect($app->request->getReferrer());

				})->name('valued_rating_delete');

				$app->map('/edit', function ($id) use ($app)
				{
					$rating = Rating::find_by_id($id);

					if ($app->request->isPost()) {
						$post = $app->request->post();
	
						$rating->update_attributes(array(
							'name' => $post['name']
						));

						$app->redirect($app->settings['urlbase_adm'].'/user/valued/rating');
					}

					$app->render('admin/user/rating/edit.html.twig', array(
						'rating' => $rating,
						'title' => 'Editando Classificação'
					));

				})->via('GET', 'POST')->name('valued_rating_edit');
			});
		});

		$app->group('/charge', function () use ($app)
		{
			$app->get('/', function () use ($app)
			{
				$charges = Charge::all(array(
					'order' => 'id DESC'
				));

				$app->render('admin/user/charge/list.html.twig', array(
					'title' => 'Cargos',
					'charges' => $charges
				));

			})->name('valued_charge_list');

			$app->post('/new', function () use ($app)
			{
				$post = $app->request->post();

				split_charges($post['name']);
				$app->redirect($app->request->getReferrer());

			})->name('valued_charge_new');

			$app->group('/:id', function ($id = null) use ($app)
			{
				$app->get('/', function ($id) use ($app)
				{
					$app->redirect($app->request->getReferrer());
				});

				$app->get('/delete', function ($id) use ($app)
				{
					$find = Charge::find_by_id($id);
					$find->delete();
					$app->redirect($app->request->getReferrer());

				})->name('valued_charge_delete');

				$app->map('/edit', function ($id) use ($app)
				{
					$charge = Charge::find_by_id($id);

					if ($app->request->isPost()) {
						$post = $app->request->post();
	
						$charge->update_attributes(array(
							'name' => $post['name']
						));

						$app->redirect($app->settings['urlbase_adm'].'/user/valued/charge');
					}

					$app->render('admin/user/charge/edit.html.twig', array(
						'charge' => $charge,
						'title' => 'Editando Classificação'
					));

				})->via('GET', 'POST')->name('valued_charge_edit');
			});
		});
	});

	$app->group('/:id', function ($id = null) use ($app)
	{
		$app->get('/', function ($id) use ($app)
		{
			$user = User::find_by_id($id);

			if ( ! $user) {
				return $app->notfound();
			}

			return $app->render('admin/user/find.html.twig', array(
				'title' => $user->name,
				'user' => $user
			));

		})->name('user_find');

		$app->group('/edit', function ($id = null) use ($app)
		{
			$app->map('/', function ($id) use ($app)
			{
				$user = User::find_by_id($id);

				if ( ! $user) 
					return $app->notfound();

				$view = array(
					'title' => 'Editando '.$user->name.' ('.$user->getProfileType().')',
					'user' => $user,

					'profile_current' => $user->profile_type
				);

				if ($user->profile_type == 'valued') {
					$view['charges'] = check_charges(Charge::all(array('order' => 'id desc')), $user);
					$view['ratings'] = check_ratings(Rating::all(array('order' => 'id desc')), $user);
					$view['groups'] = check_groups(Group::all(array('order' => 'id desc')), $user);
				}

				if ($app->request->isPost()) {

					$post = $view['value'] = $app->request->post();
					$password = trim(strip_tags($post['password']));

					$attributes = array(
						'name' => $post['name'],
						'email' => $post['email'],
						'profile_type' => $post['profile_type'],
						'birth' => date_db($post['birth']),
						'graduated_at' => date_db($post['graduated_at']),
						'salary' => money($post['salary']),
						'entry_at' => date_db($post['entry_at'])
					);

					if ($attributes['profile_type'] == 'admin') {
						$attributes['username'] = $post['username'];

						if ($password != '') {
							$attributes['password'] = User::crypt_password($password);								
						}						
					}



					$save = $user->update_attributes($attributes);

					if ( $save !== true) {
						$app->flash('errors', array_values($user->errors->full_messages()));
						return $app->redirect($app->request->getReferrer());
					} 

					if ($attributes['profile_type'] == 'admin') {

						$users = User::all(array('conditions' => array('username=? AND profile_type=?', $attributes['username'], 'admin')));

						if ( ! $users) {
							$app->flash('errors', array('Usuário não localizado.'));
							$app->redirect($app->request->getReferrer());
						}

						if (isset($attributes['password'])) {
							
							$test_login = false;

							foreach ($users as $user) {
								if (crypt($password, $user->password) == $user->password) {
									$test_login = true;
									break;
								}
							}

							if ( ! $test_login) {
								$app->flash('errors', array('Teste automático de login: FALHOU'));
								return $app->redirect($app->request->getReferrer());
							}
						}						
					}

					$app->flash('success', array('As alterações foram salvas.'));

					if (isset($post['save'])) {
						return $app->redirect($app->request->getReferrer());
					}

					return $app->redirect($app->settings['urlbase_adm'] . '/user/' . $user->id);
				}

				return $app->render('admin/user/edit.html.twig', $view);

			})->via('GET', 'POST')->name('user_edit');

			$app->post('/evaluations', function ($id) use ($app)
			{
				$evaluator = User::find_by_id($id);
					
				$post = $app->request->post();

				$action = $post['action'];
				$evaluator_id = $post['evaluator_id'];
				$evaluation_id = $post['evaluation_id'];

				$options = array(
					'evaluation_id' => $evaluation_id,
					'evaluator_id' => $evaluator_id
				);

				switch ($action) {

					case 'add':
						$create = EvaluationEvaluator::create($options);
						die(EvaluationEvaluator::count(array('conditions' => array('evaluator_id=?', $evaluator_id))));
						break;

					case 'rm':
						EvaluationEvaluator::table()->delete($options);
						die(EvaluationEvaluator::count(array('conditions' => array('evaluator_id=?', $evaluator_id))));
						break;
					
					default:
						die(json_encode(false));
						break;
				}
			});

			$app->post('/rating', function ($id) use ($app)
			{
				$post = $app->request->post();

				$action = $post['action'];
				$rating_id = $post['rating_id'];
				$user_id = $post['user_id'];

				$options = array(
					'user_id' => $user_id,
					'rating_id' => $rating_id
				);

				switch ($action) {

					case 'add':
						$create = UserRating::create($options);
						die('true');
						break;

					case 'rm':
						UserRating::table()->delete($options);
						die('true');
						break;
					
					default:
						die('false');
						break;
				}
			})->name('user_edit_rating');

			$app->post('/charge', function ($id) use ($app)
			{
				$post = $app->request->post();

				$action = $post['action'];
				$charge_id = $post['charge_id'];
				$user_id = $post['user_id'];

				$options = array(
					'user_id' => $user_id,
					'charge_id' => $charge_id
				);

				switch ($action) {

					case 'add':
						$create = UserCharge::create($options);
						die('true');
						break;

					case 'rm':
						UserCharge::table()->delete($options);
						die('true');
						break;
					
					default:
						die('false');
						break;
				}
			})->name('user_edit_charge');

		});

		$app->get('/delete', function ($id) use ($app)
		{
			$delete = User::transaction( function () use ($id) {
				$user = User::find_by_id($id);
				if ($user) $user->delete();
			});

			if ($delete)
				$app->redirect($app->request->getReferrer());

		})->name('user_delete');
	});

	$app->map('/new(/:profile_type)', function ($profile_type = 'valued') use ($app)
	{
		$view = array(
			'title' => 'Cadastro'
		);

		if ($app->request->isPost()) {

			$post = $view['value'] = $_SESSION['value'] = $app->request->post();

			$editing = isset($post['save_edit']);



			$find = User::find_by_email($post['email']);

			if ($find and in_array($find->profile_type, array('admin', $post['profile_type']))) {
				$app->flash('errors', array('E-mail ja cadastrado com este perfil.'));
				$app->redirect($app->request->getReferrer());
			}

			$data = array(
				'name' => $post['name'],
				'email' => $post['email'],
				'profile_type' => $post['profile_type'],
				'birth' => dateBRtoUS($post['birth']),
				'graduated_at' => $post['graduated_at'],
				'salary' => money($post['salary']),
				'entry_at' => dateBRtoUS($post['entry_at'])
			);

			$user = User::create($data);

			if ($user->is_valid()) {

				$app->flash('success', array('Usuário Cadastrado.'));
				$app->redirect($app->settings['urlbase_adm'] . '/user/' . $user->id . ($editing ? '/edit' : ''));
			} else {
				$app->flash('errors', array_values($user->errors->full_messages()));
				$app->redirect($app->request->getReferrer());
			}
		}

		$view['ratings'] = check_ratings(Rating::all());
		$view['charges'] = check_charges(Charge::all());
		$view['profile_types'] = User::$profile_types;
		$view['profile_current'] = $profile_type;

		return $app->render('admin/user/new.html.twig', $view);

	})->via('GET', 'POST')->name('user_new');
});
?>