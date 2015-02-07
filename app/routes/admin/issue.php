<?php

$app->group('/issue', function () use ($app)
{
	$app->get('/', function () use ($app)
	{
		$view = array(
			'title' => 'Lista',
			'issues' => Issue::all(),
			'types' => Issue::$types
		);

		return $app->render('admin/issue/list.html.twig', $view);
	});

	$app->group('/:issue_id', function ($issue_id = null) use ($app)
	{
		$app->group('/edit', function ($issue_id = null) use ($app)
		{
			$app->map('(/questionnaire/:questionnaire_id)', function ($issue_id, $questionnaire_id = null) use ($app)
			{
				$issue = Issue::find_by_id($issue_id);

				if ( ! $issue)
					return $app->notfound();

				if ($app->request->isPost()) {
				
					$post = $app->request->post();
					$issue->update_attributes(Issue::getAttributes($issue->type, $post, true));

					if ( $issue->is_invalid()) {
						$app->flash('errors', $issue->errors->full_messages());
						return $app->redirect($app->request->getReferrer());
					}

					if ($issue->type == 'scale')
						$scales = $issue->editScales($post);

					if (in_array($issue->type, array('only_choice', 'multiple_choice'))) {
						$alternatives = isset($post['alternatives']) ? $post['alternatives'] : '';
						$issue->editAlternatives($alternatives);
					}

					if (isset($post['questionnaire_id']))
						return $app->redirect($app->settings['urlbase_adm'].'/questionnaire/'.$post['questionnaire_id'].'/edit'.'#issue_id_'.$issue->id);

					$app->flash('success', array('Atualizada com sucesso!'));
					return $app->redirect($app->request->getReferrer());
				}

				$view = array(
					'title' => Issue::$types[$issue->type],
					'issue' => $issue,
					'type' => $issue->type,
					'edit' => 1
				);

				if ($questionnaire_id) {
					$view['questionnaire_id'] = $questionnaire_id;
				}

				if (in_array($issue->type, array('only_choice', 'multiple_choice')))
					$view['alternatives'] = $issue->alternatives_for_textarea();

				if (in_array($issue->type, array('scale')))
					$view['scales'] = Scale::all();

				return $app->render('admin/issue/edit_'.$issue->type.'.html.twig', $view);

			})->via('GET', 'POST');

			$app->post('/value', function ($issue_id) use ($app)
			{
				$post = $app->request->post();
				$questionnaire_issue = QuestionnaireIssue::find_by_questionnaire_id_and_issue_id($post['questionnaire_id'], $issue_id);

				$questionnaire_issue->update_attributes(array(
					'value' => (! $post['value'] ? 0.5 : ($post['value'] > 20 ? 20 : $post['value']))
				));

				if ($app->request->isAjax()) {
					die('true');
				}

				return $app->redirect($app->request->getReferrer());
			});

			$app->group('/order', function ($issue_id = null) use ($app)
			{
				$app->post('/', function ($issue_id) use ($app)
				{
					$post = $app->request->post();
					$questionnaire_issue = QuestionnaireIssue::find_by_questionnaire_id_and_issue_id($post['questionnaire_id'], $issue_id);

					$questionnaire_issue->update_attributes(array(
						'order' => $post['order']
					));

					if ($app->request->isAjax()) {
						die('true');
					}

					return $app->redirect($app->request->getReferrer());
				});

				$app->get('/:action', function ($issue_id, $action) use ($app)
				{
					$issue = Issue::find_by_id($issue_id);

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

					return $app->redirect($app->request->getReferrer().'#issue_id_'.$issue->id);
				});
			});
		});
		
		$app->get('/copy(/questionnaire/:questionnaire_id)', function ($issue_id, $questionnaire_id = null) use ($app)
		{
			$issue = Issue::find_by_id($issue_id);
			$copy = $issue->copy($questionnaire_id);

			return $app->redirect($app->settings['urlbase_adm'].'/issue/'.$copy->id.'/edit'.($questionnaire_id ? '/questionnaire/'.$questionnaire_id : ''));
		});

		$app->get('/delete', function ($issue_id) use ($app)
		{
			$issue = Issue::find_by_id($issue_id);

			if ($issue)
				$issue->delete();

			if ($app->request->isAjax()) die('true');

			return $app->redirect($app->settings['urlbase_adm'].'/issue');

		})->name('issue_delete');
	});

	$app->group('/new', function () use ($app)
	{
		$app->get('/', function () use ($app)
		{
			return $app->render('admin/issue/new.html.twig', array(
				'title' => 'Selecionar tipo de Pergunta',
				'types' => Issue::$types
			));
		});

		$app->map('/:type(/questionnaire/:questionnaire_id)', function ($type, $questionnaire_id = null) use ($app)
		{
			$get = $app->request->get();

			if ($app->request->isPost()) {
				
				$post = $app->request->post();
				$attributes = Issue::getAttributes($type, $post);
				$issue = Issue::create($attributes);
				
				if ($issue->is_invalid()) {
					$app->flash('errors', $issue->errors->full_messages());
					return $app->redirect($app->request->getReferrer());
				}

				$apply_questionnaire = $issue->apply_questionnaire($post);

				if (in_array($type, array('only_choice', 'multiple_choice'))) {
					$alternatives = isset($post['alternatives']) ? $post['alternatives'] : '';
					$issue->editAlternatives($alternatives);
				}

				if ($type == 'scale')
					$scales = $issue->editScales($post);

				if (isset($post['questionnaire_id'])) {
					return $app->redirect($app->settings['urlbase_adm'].'/questionnaire/'.$post['questionnaire_id'].'/edit#issue_id_'.$issue->id);
				}

				return $app->redirect($app->settings['urlbase_adm'].'/issue/'.$issue->id.'/edit');

				return $app->redirect($app->request->getReferrer().'#issue_id_'.$issue->id);
			}

			$view = array(
				'title' => 'Nova Questão '.Issue::$types[$type],
				'type' => $type,
				'types' => Issue::$types
			);

			if ($questionnaire_id)
				$view['questionnaire_id'] = $questionnaire_id;

			if ($type == 'scale')
				$view['scales'] = Scale::all();

			return $app->render('admin/issue/edit_'.$type.'.html.twig', $view);

		})->via('GET', 'POST');
	});
});