<?php

$app->group('/questionnaire', function () use ($app)
{
	$app->get('/', function () use ($app)
	{
		$questionnaires = Questionnaire::all(array(
			'order' => 'created_at DESC'
		));

		$view = array(
			'title' => 'Lista',
			'questionnaires' => $questionnaires
		);

		return $app->render('admin/questionnaire/list.html.twig', $view);

	})->name('questionnaire_list');

	$app->map('/new(/copy(/:questionnaire_id))', function ($copy = false) use ($app)
	{
		if ($app->request->isPost()) {

			$post = $app->request->post();
			$_SESSION['value'] = $post;

			$user_id = User::getUserLogger('id');
			$action = isset($post['action']) ? $post['action'] : 'new';

			switch ($action) {
				case 'new':

					if ( strlen($post['name_new']) < 3) {
						$app->flash('errors', array('Informe o itulo do Questionário.'));
						return $app->redirect($app->settings['urlbase_adm'].'/questionnaire/new');
					}

					$post['name_new_private'] = $post['name_new_private'] == '' ? $post['name_new'] : $post['name_new_private'];

					$questionnaire = Questionnaire::create(array(
						'name' => $post['name_new'],
						'name_private' => $post['name_new_private'],
						'user_id' => $user_id
					));

					if ($questionnaire->is_invalid()) {

						$app->flash('errors', array_values($questionnaire->errors->full_messages()));
						return $app->redirect($app->settings['urlbase_adm'].'/questionnaire/new');
					}

					break;

				case 'copy':

					$url = $app->settings['urlbase_adm'].'/questionnaire/new/copy';

					if ( $post['questionnaire_id'] == '') {
						$app->flash('errors', array('O Questionário a ser duplicado não foi informado.'));
						return $app->redirect($url);
					}

					$post['name_copy_private'] = $post['name_copy_private'] == '' ? $post['name_copy'] : $post['name_copy_private'];

					$find = Questionnaire::find_by_id($post['questionnaire_id']);
					$questionnaire = $find->copy(array(
						'name' => $post['name_copy'],
						'name_private' => $post['name_copy_private'],
						'user_id' => User::getUserLogger('id')
					));

					if (is_array($questionnaire)) {
						$app->flash('errors', $questionnaire);
						return $app->redirect($url.'/'.$post['questionnaire_id']);
					}

					break;
			}

			$app->flash('success', array('Sucesso'));
			$app->redirect($app->settings['urlbase_adm'].'/questionnaire/'.$questionnaire->id.'/edit');
		}

		$questionnaires = Questionnaire::all();

		return $app->render('admin/questionnaire/new.html.twig', array(
			'title' => 'Criar',
			'questionnaires' => $questionnaires,
			'copy' => ( ! $copy ? strpos($_SERVER['REQUEST_URI'], 'copy') : $copy)
		));

	})->via('GET', 'POST')->name('questionnaire_new');

	$app->group('/:questionnaire_id', function ($questionnaire_id = null) use ($app)
	{
		$app->get('/', function ($questionnaire_id) use ($app)
		{
			$questionnaire = Questionnaire::find_by_id($questionnaire_id);

			if ( ! $questionnaire)
				return $app->notfound();

			if (count($questionnaire->questionnaire_issues) == 0) {
				return $app->redirect($app->settings['urlbase_adm'].'/questionnaire/'.$questionnaire_id.'/edit');
			}				

			$view = array(
				'title' => $questionnaire->name_private,
				'questionnaire' => $questionnaire
			);

			return $app->render('admin/questionnaire/issue.html.twig', $view);

		})->name('questionnaire_find');

		$app->get('/show', function ($questionnaire_id) use ($app)
		{
			$questionnaire = Questionnaire::find_by_id($questionnaire_id);

			$view = array(
				'title' => $questionnaire->name_private,
				'questionnaire' => $questionnaire,
				'issues' => $questionnaire->issues
			);

			return $app->render('admin/questionnaire/show.html.twig', $view);

		})->name('questionnaire_find');

		$app->group('/edit', function ($questionnaire_id = null) use ($app)
		{
			$app->get('/', function ($questionnaire_id) use ($app)
			{
				$questionnaire = Questionnaire::find_by_id($questionnaire_id);

				if ( ! $questionnaire->can_edit()) {
					$app->flash('errors', array('Este Questionário esta sendo usado em avaliação, portanto não pode ser editado.'));
					return $app->redirect($app->settings['urlbase_adm'].'/questionnaire');
				}

				$get = $app->request->get();

				$response = isset($get['response']) ? json_decode($get['response']) : null;

				$issues = $questionnaire->issues;

				$view = array(
					'title' => $questionnaire->name_private,
					'response' => $response,
					'questionnaire' => $questionnaire,
					'questionnaire_issues' => $issues,
					'issues' => Issue::all(),
					'types' => Issue::$types,
					'issue_types' => Issue::$types
				);

				return $app->render('admin/questionnaire/issue_edit.html.twig', $view);

			})->name('questionnaire_edit');

			$app->post('/name', function ($questionnaire_id) use ($app)
			{
				$post = $app->request->post();

				$questionnaire = Questionnaire::find_by_id($questionnaire_id);

				if ($questionnaire) {
					$questionnaire->update_attributes(array(
						'name' => $post['name'],
						'name_private' => $post['name_private']
					));
				}
				
				return $app->redirect($app->request->getReferrer());

			})->name('questionnaire_edit_issue');

			$app->group('/issue', function () use ($app)
			{
				$app->post('/', function ($questionnaire_id) use ($app)
				{
					$post = $app->request->post();

					if ( ! $post['issue_id']) {
						return $app->redirect($app->request->getReferrer());
					}

					QuestionnaireIssue::uniqueness(array(
						'questionnaire_id' => $questionnaire_id,
						'issue_id' => $post['issue_id'],
						'order' => 10
					));

					return $app->redirect($app->request->getReferrer());

				})->name('questionnaire_edit_issue');

				$app->group('/:issue_id', function ($questionnaire_id = null, $issue_id = null) use ($app)
				{
					$app->get('/', function ($questionnaire_id, $issue_id) use ($app)
					{
						$find = QuestionnaireIssue::find(array(
							'issue_id' => $issue_id,
							'questionnaire_id' => $questionnaire_id
						));

						$find->delete();

						return $app->redirect($app->request->getReferrer());
					});

					$app->get('/order/:action', function ($questionnaire_id, $issue_id, $action) use ($app)
					{
						$issue = QuestionnaireIssue::find(array(
							'questionnaire_id' => $questionnaire_id,
							'issue_id' => $issue_id
						));

						switch ($action) {
							case 'up':
								$order = $issue->order + 1;
								break;
							
							default:
								$order = $issue->order > 0 ? $issue->order - 1 : 0;
								break;
						}

						$issue->update_attributes(array(
							'order' => $order
						));

						return $app->redirect($app->request->getReferrer().'#issue_id_'.$issue_id);
					});

					$app->get('/delete', function ($questionnaire_id, $issue_id) use ($app)
					{
						$find = QuestionnaireIssue::find(array(
							'issue_id' => $issue_id,
							'questionnaire_id' => $questionnaire_id
						));

						if ($find)
							$find->delete();						

						return $app->redirect($app->request->getReferrer());
					});
				});

				$app->get('/', function ($questionnaire_id) use ($app)
				{
					$questionnaire = Questionnaire::find_by_id($questionnaire_id);
					$questionnaire->delete();

					return $app->redirect($app->request->getReferrer());

				})->name('questionnaire_delete');
			});

			$app->post('/evaluations', function ($questionnaire_id) use ($app)
			{
				$questionnaire = Questionnaire::find_by_id($questionnaire_id);
					
				$post = $app->request->post();

				$action = $post['action'];
				$questionnaire_id = $post['questionnaire_id'];
				$evaluation_id = $post['evaluation_id'];

				$options = array(
					'evaluation_id' => $evaluation_id,
					'questionnaire_id' => $questionnaire_id
				);

				switch ($action) {

					case 'add':
						$create = EvaluationQuestionnaire::create($options);
						die(EvaluationQuestionnaire::count(array('conditions' => array('questionnaire_id=?', $questionnaire_id))));
						break;

					case 'rm':
						EvaluationQuestionnaire::table()->delete($options);
						die(EvaluationQuestionnaire::count(array('conditions' => array('questionnaire_id=?', $questionnaire_id))));
						break;
					
					default:
						die(json_encode(false));
						break;
				}
			});
		});

		$app->get('/delete', function ($questionnaire_id) use($app)
		{
			$questionnaire = Questionnaire::find_by_id($questionnaire_id);

			if ($questionnaire) {
				if ( ! $questionnaire->delete()) {
					$app->flash('errors', 'Questionário cadastrado em avaliação '.$questionnaire->evaluationNames().'. Portanto não pode ser removido.');
					return $app->redirect($app->request->getReferrer());
				}
			}
			
			return $app->redirect($app->request->getReferrer());
		});

		$app->map('/issue', function ($questionnaire_id) use ($app)
		{
			$questionnaire = Questionnaire::find_by_id($questionnaire_id);
			$issues = $questionnaire->issues;

			$view = array(
				'title' => $questionnaire->name_private,
				'questionnaire' => $questionnaire,
				'issues' => $issues
			);

			return $app->render('admin/questionnaire/issue.html.twig', $view);
			
		})->via('GET', 'POST')->name('questionnaire_issue_list');
	});
});