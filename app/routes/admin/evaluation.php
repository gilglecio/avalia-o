<?php

$app->group('/evaluation', function () use ($app) {

	$app->get('/', function () use ($app)
	{
		$evaluations = Evaluation::all(array(
			'order' => 'created_at DESC'
		));

		$view = array(
			'title' => 'Eventos',
			'evaluations' => $evaluations
		);

		return $app->render('admin/evaluation/list.html.twig', $view);
	})->name('evaluation_list');

	$app->get('/sending/:evaluation_sending_id/delete', function ($evaluation_sending_id) use ($app)
	{
		$evaluation_sending = EvaluationSending::find_by_id($evaluation_sending_id);

		if ($evaluation_sending)
			$evaluation_sending->delete();

		return $app->redirect($app->request->getReferrer());
	});

	$app->group('/:id', function ($evaluation_id = null) use ($app)
	{
		$app->get('/', function ($evaluation_id) use ($app)
		{
			$evaluation = Evaluation::find_by_id($evaluation_id);

			$view = array(
				'title' => $evaluation->name,
				'evaluation' => $evaluation
			);

			return $app->render('admin/evaluation/show.html.twig', $view);

		})->name('evaluation_find');

		$app->get('/delete', function ($evaluation_id) use ($app)
		{
			$evaluation = Evaluation::find_by_id($evaluation_id);
			$evaluation->delete();

			return $app->redirect($app->request->getReferrer());

		})->name('evaluation_delete');

		$app->get('/sending', function ($evaluation_id) use ($app)
		{
			$evaluation = Evaluation::find_by_id($evaluation_id);
			$evaluation_sendings = $evaluation->evaluation_sendings;

			$view = array(
				'title' => 'Envios',
				'evaluation_sendings' => $evaluation_sendings,
				'evaluation' => $evaluation
			);

			return $app->render('admin/evaluation/sending.html.twig', $view);

		})->name('evaluation_delete');

		$app->get('/send', function ($evaluation_id) use ($app)
		{
			$evaluation = Evaluation::find_by_id($evaluation_id);
			$errors = array();

			// verificar usuarios
			if (count($evaluation->evaluation_groups)) {

				foreach ($evaluation->evaluation_groups as $evaluation_group) {
					if ( ! count($evaluation_group->group->group_members)) {
						array_push($errors, 'Grupo '.$evaluation_group->group->name.': VAZIO');
					}
				}

			} else {
				array_push($errors, 'Nenhum grupo foi adicionado');
			}

			// verificar questoes
			if (count($evaluation->evaluation_questionnaires)) {

				foreach ($evaluation->questionnaires as $questionnaire) {
					if ( ! count($questionnaire->questionnaire_issues)) {
						array_push($errors, 'Questionário '.$questionnaire->name.': VAZIO');
					}
				}

			} else {
				array_push($errors, 'Nenhum Questionário foi adicionado');
			}

			if ( ! in_array($evaluation->getStatus(), array('Aberta'))) {
				array_push($errors, 'Avaliação '.$evaluation->getStatus());
			}

			if ( ! empty($errors)) {
				$app->flash('errors', $errors);
				return $app->redirect($app->settings['urlbase_adm'].'/evaluation/'.$evaluation->id.'/sending');
			}

			$evaluation_sending = EvaluationSending::create(array(
				'evaluation_id' => $evaluation_id
			));

			$evaluation = Evaluation::find_by_id($evaluation_id);
			$sendings = $evaluation->sent($evaluation_sending);

			return $app->redirect($app->settings['urlbase_adm'] . '/evaluation/'.$evaluation_id.'/sending');

		})->name('evaluation_delete');

		$app->map('/edit', function ($evaluation_id) use ($app)
		{
			$evaluation = Evaluation::find_by_id($evaluation_id);

			if ($app->request->isPost()) {

				$post = $app->request->post();
				
				$post['start_at'] = joinDateTime($post['start_at_date'], $post['start_at_time']);
				$post['end_at'] = joinDateTime($post['end_at_date'], $post['end_at_time']);

				$errors = array();

				if (strlen($post['start_at']) == 16) {
					
					$valid = new Data_Validator;
					if ( ! $valid->set('Data Inicial', $post['start_at'])->is_date('yyyy-mm-dd H:i')) {
						array_push($errors, 'Data Inicial Inválida');
					}
				} elseif ($post['start_at'] == '') {
					array_push($errors, 'Data Inicial é Obrigatória');
				} else {
					array_push($errors, 'Data Inicial não informada corretamente. Obedeça o formato (DD/MM/YYYY HH:MM)');
				}

				if (strlen($post['end_at']) == 16) {
					
					$valid = new Data_Validator;

					if ( ! $valid->set('Data Final', $post['end_at'])->is_date('yyyy-mm-dd H:i')) {
						array_push($errors, 'Data Final Inválida');
					}
				} elseif ($post['end_at'] == '') {
					array_push($errors, 'Data Final é Obrigatória');
				} else {
					array_push($errors, 'Data Final não informada corretamente. Obedeça o formato (DD/MM/YYYY HH:MM)');
				}

				if ( ! empty($errors)) {
					$app->flash('errors', $errors);
					$app->redirect($app->settings['urlbase_adm'] . '/evaluation/'.$evaluation->id.'/edit');
				}

				$post['mail_bcc'] = Mail::parseBcc($post['mail_bcc'], true);

				$save = isset($post['save']);

				unset(
					$post['save'],
					$post['finalize'],
					$post['csrf_token'], 
					$post['start_at_date'],
					$post['start_at_time'],
					$post['end_at_date'],
					$post['end_at_time']
				);

				$update = $evaluation->update_attributes($post);

				if ($update) {
					$app->flash('success', array('Salvo'));
				} else {
					$app->flash('errors', array_values($evaluation->errors->full_messages()));
					$app->redirect($app->settings['urlbase_adm'] . '/evaluation/'.$evaluation->id.'/edit');
				}

				if ($save) {
					return $app->redirect($app->request->getReferrer());
				}

				return $app->redirect($app->settings['urlbase_adm'].'/evaluation');
			}

			$evaluators = User::all(array(
				'conditions' => array('profile_type=?', 'appraiser')
				)
			);

			$view = array(
				'title' => 'Editando '.$evaluation->name,
				'evaluation' => $evaluation,
				'questionnaires' => check_evaluation_questionnaires(Questionnaire::all(), $evaluation),
				'groups' => check_evaluation_groups(Group::all(array('order' => 'id DESC')), $evaluation),
				'evaluators' => check_evaluation_evaluators($evaluators, $evaluation)
			);

			return $app->render('admin/evaluation/edit.html.twig', $view);

		})->via('GET', 'POST')->name('evaluation_edit');
	});

	$app->map('/new(/copy(/:evaluation_id))', function ($copy = false) use ($app)
	{
		$view = array(
			'title' => 'Criar Avaliação',
			'copy' => ($copy ? $copy : strpos($_SERVER['REQUEST_URI'], 'copy')),
			'evaluations' => Evaluation::all()
		);

		if ($app->request->isPost()) {
			
			$post = $app->request->post();
			$_SESSION['value'] = $post;
			
			$user_id = User::getUserLogger('id');

			if ( ! $user_id)
				return $app->redirect(config('domain').'/logout');

			$action = isset($post['action']) ? $post['action'] : 'new';

			switch ($action) {
				case 'new':
					$mail_bcc = User::mailIsValid($post['mail_bcc']);

					$start_at = trim(joinDateTime($post['start_at_date'], $post['start_at_time']));
					$end_at = trim(joinDateTime($post['end_at_date'], $post['end_at_time']));

					$errors = array();

					if (strlen($start_at) == 16) {
						
						$valid = new Data_Validator;
						if ( ! $valid->set('Data Inicial', $start_at)->is_date('yyyy-mm-dd H:i')) {
							array_push($errors, 'Data Inicial Inválida');
						}
					} elseif ($start_at == '') {
						array_push($errors, 'Data Inicial é Obrigatória');
					} else {
						array_push($errors, 'Data Inicial não informada corretamente. Obedeça o formato (DD/MM/YYYY HH:MM)');
					}

					if (strlen($end_at) == 16) {
						
						$valid = new Data_Validator;
						if ( ! $valid->set('Data Final', $end_at)->is_date('yyyy-mm-dd H:i')) {
							array_push($errors, 'Data Final Inválida');
						}
					} elseif ($end_at == '') {
						array_push($errors, 'Data Final é Obrigatória');
					} else {
						array_push($errors, 'Data Final não informada corretamente. Obedeça o formato (DD/MM/YYYY HH:MM)');
					}

					if ( ! empty($errors)) {
						$app->flash('errors', $errors);
						$app->redirect($app->settings['urlbase_adm'] . '/evaluation/new');
					}

					$attributes = array(
						'user_id' => $user_id,
						'name' => $post['name'],
						'subject' => $post['subject'],
						'start_at' => $start_at,
						'end_at' => $end_at,
						'message_email' => $post['message_email'],
						'mail_bcc' => Mail::parseBcc($post['mail_bcc'], true),
						'status' => 0
					);

					$evaluation = Evaluation::create($attributes);

					if ($evaluation->is_invalid()) {
						$app->flash('errors', array_values($evaluation->errors->full_messages()));
						$app->redirect($app->settings['urlbase_adm'] . '/evaluation/new');
					}

					break;

				case 'copy':

					if ( $post['evaluation_id'] == '') {
						$app->flash('errors', array('Avaliação não Selecionada'));
						$app->redirect($app->settings['urlbase_adm'].'/evaluation/new/copy');
					}

					$start_at = joinDateTime($post['copy_start_at_date'], $post['copy_start_at_time']);
					$end_at = joinDateTime($post['copy_end_at_date'], $post['copy_end_at_time']);

					$errors = array();

					if (strlen($start_at) == 16) {
						
						$valid = new Data_Validator;
						if ( ! $valid->set('Data Inicial', $start_at)->is_date('yyyy-mm-dd H:i')) {
							array_push($errors, 'Data Inicial Inválida');
						}
					} elseif ($start_at == '') {
						array_push($errors, 'Data Inicial é Obrigatória');
					} else {
						array_push($errors, 'Data Inicial não informada corretamente. Obedeça o formato (DD/MM/YYYY HH:MM)');
					}

					if (strlen($end_at) == 16) {
						
						$valid = new Data_Validator;
						if ( ! $valid->set('Data Final', $end_at)->is_date('yyyy-mm-dd H:i')) {
							array_push($errors, 'Data Final Inválida');
						}
					} elseif ($end_at == '') {
						array_push($errors, 'Data Final é Obrigatória');
					} else {
						array_push($errors, 'Data Final não informada corretamente. Obedeça o formato (DD/MM/YYYY HH:MM)');
					}

					if ( ! empty($errors)) {
						$app->flash('errors', $errors);
						$app->redirect($app->settings['urlbase_adm'] . '/evaluation/new/copy');
					}

					$find = Evaluation::find_by_id($post['evaluation_id']);

					$evaluation = $find->copy(array(
						'name' => $post['name_copy'],
						'user_id' => $user_id,
						'start_at' => $start_at,
						'end_at' => $end_at
					));

					if (is_array($evaluation)) {
						
						$app->flash('errors', $evaluation);
						$app->redirect($app->settings['urlbase_adm'] . '/evaluation/new/copy/'.$find->id);
					}

					break;
			}

			$app->flash('success', array('Sucesso'));
			
			return $app->redirect($app->settings['urlbase_adm'] . '/evaluation/'.$evaluation->id.'/edit');
		}

		return $app->render('admin/evaluation/new.html.twig', $view);
		
	})->via('GET', 'POST')->name('evaluation_new');

	$app->get('/result', function () use ($app)
	{
		return $app->render('admin/evaluation/result.html.twig', array(
			'title' => 'Analise de Resultados'
		));
	});
});