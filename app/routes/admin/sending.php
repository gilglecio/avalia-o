<?php

$app->group('/sending', function () use ($app) {

	$app->get('/', function () use ($app)
	{
		$sendings = Sending::all();

		$view = array(
			'title' => 'Envios',
			'sendings' => $sendings
		);

		return $app->render('admin/sending/list.html.twig', $view);

	})->name('sending_list');

	$app->group('/sending/:sending_id', function ($sending_id = null) use($app)
	{
		$app->get('/correction(/evaluator/:evaluator_id)', function ($sending_id = null, $evaluator_id = null) use ($app)
		{
			$data = Correction::bySending($sending_id);
			$sending = Sending::find_by_id($sending_id);

			if ( ! $sending) {
				return $app->notfound();
			}

			if ( ! $evaluator_id) {
				foreach ($data['evaluators'] as $key => $value) {
					$evaluator_id = $data['evaluators'][$key]->id;
					break;
				}
			}
			$evaluation = $sending->evaluation_sending->evaluation;
			$evaluator = User::find_by_id($evaluator_id);

			if (empty($data['media']) OR ! $evaluator_id) {
				$view['media'] = 'NULL';
				// $app->flash('errors', array('O avaliador foi removido.'));
			} else {
				$view['media'] = $data['media'][$evaluator_id];
			}

			if (empty($data['sum']) OR ! $evaluator_id) {
				$view['sum'] = 'NULL';
				// $app->flash('errors', array('O avaliador foi removido.'));
			} else {
				$view['sum'] = $data['sum'][$evaluator_id];
			}
			
			$view = array(
				'title' => 'Correção',
				'data' => $data,
				'sending_id' => $sending_id,
				'sending' => $sending,
				'evaluation' => $evaluation,
				'valued' => $sending->valued,
				'evaluator' => $evaluator,
				'evaluator_id' => $evaluator_id
			);

			return $app->render('admin/sending/correction.html.twig', $view);
		});

		$app->get('/print', function ($sending_id = null) use ($app)
		{
			$sending = Sending::find_by_id($sending_id);

			if ( ! $sending)
				return $app->notfound();

			$evaluation = $sending->evaluation_sending->evaluation;

			$valued = User::find_by_id($sending->valued_id);
			$answers = Answer::find_all_by_valued_id_and_evaluation_sending_id($sending->valued_id, $sending->evaluation_sending_id);
			$data = Correction::bySending($sending_id);

			return $app->render('admin/sending/print_answers.html.twig', array(
				'title' => 'Avaliação: '.$evaluation->name.' - Funcionário: '.$valued->name,
				'answers' => $answers,
				'evaluation' => $evaluation,
				'valued' => $valued,
				'data' => $data,
				'sending_id' => $sending->id,
				'evaluator' => array(
					'name' => $sending->valued->name,
					'email' => $sending->valued->email
				)
			));
		});

		$app->post('/pdf/available', function ($sending_id = null) use ($app)
		{
			$post = $app->request->post();
			$pdf = Pdf::find_by_sending_id_and_evaluator_id($sending_id, $post['evaluator_id']);
			
			if ($pdf) {
				$pdf->update_attributes(array(
					'is_available' => $post['available']
				));
			}
			
		});

		$app->map('/pdf(/evaluator/:evaluator_id)(/layout/:layout)', function ($sending_id = null, $evaluator_id = null, $layout = null) use ($app)
		{
			
			$sending = Sending::find_by_id($sending_id);
			$data = Correction::bySending($sending_id);

			if ( ! $evaluator_id) {
				foreach ($data['evaluators'] as $key => $value) {
					$evaluator_id = $data['evaluators'][$key]->id;
					break;
				}
			}

			$pdf = Pdf::find_by_sending_id_and_evaluator_id($sending_id, $evaluator_id);

			if ($app->request->isPost()) {
				$post = $app->request->post();	

				unset($post['csrf_token']);

				$post['salary'] = money($post['salary']);
				$post['bonus'] = money($post['bonus']);
				$post['final_note'] = money($post['final_note']);
				$post['new_salary'] = money($post['new_salary']);

				$post['perf'] = money($post['perf']);
				$post['evaluator_note'] = money($post['evaluator_note']);
				$post['nr_salary'] = money($post['nr_salary']);
				$post['nr_salary_prop'] = money($post['nr_salary_prop']);

				if ($pdf) {
					$pdf->update_attributes($post);
				} else {
					$pdf = Pdf::create($post);	
				}

				return $app->redirect($app->request->getReferrer());
			}
				
			$evaluator = User::find_by_id($evaluator_id);

			$sum_issue_0_10 = 0;

			$answers = Answer::find_all_by_valued_id_and_evaluation_sending_id($sending->valued_id, $sending->evaluation_sending_id);

			foreach ($answers as $answer) {
				if ($answer->issue->type == '0-10') {
					$sum_issue_0_10 += $answer->answer;
				}
			}

			$view = array(
				'title' => 'PDF',
				'pdf' => $pdf,
				'sending' => $sending,
				'data' => $data,
				'sum_issue_0_10' => number_format($sum_issue_0_10, 2, ',', '.'),
				'evaluator' => $evaluator,
				'layout' => $layout,
				'evaluator_id' => $evaluator_id
			);

			if (empty($data['media']) OR ! $evaluator_id) {
				$view['media'] = 'NULL';
				// $app->flash('errors', array('O avaliador foi removido.'));
			} else {
				$view['media'] = number_format($data['media'][$evaluator_id], 2, ',', ' ');
			}

			if (empty($data['sum']) OR ! $evaluator_id) {
				$view['sum'] = 'NULL';
				// $app->flash('errors', array('O avaliador foi removido.'));
			} else {
				$view['sum'] = number_format($data['sum'][$evaluator_id], 2, ',', ' ');
			}

			if ($layout) {
				return $app->render('admin/sending/pdf_print.html.twig', $view);
				// ob_start();
				// $PDF = ob_get_contents();
				// dd($PDF, true);
				// ob_clean($PDF);
				//return $app->render('admin/sending/pdf_print.html.twig', $view);
			}

			return $app->render('admin/sending/pdf.html.twig', $view);

		})->via('GET', 'POST');
	});

	$app->group('/:evaluation_sending_id', function ($evaluation_sending_id = null) use ($app)
	{
		$app->get('/', function ($evaluation_sending_id) use ($app)
		{
			$sendings = Sending::find_all_by_evaluation_sending_id($evaluation_sending_id);
			
			if ( ! $sendings )
				return $app->notfound();

			$evaluation = $sendings[0]->evaluation_sending->evaluation;

			$view = array(
				'title' => 'Funcionários',
				'sendings' => $sendings,
				'evaluation_sending_id' => $evaluation_sending_id,
				'evaluation' => $evaluation
			);

			return $app->render('admin/sending/list.html.twig', $view);

		})->name('sending_find');

		$app->post('/remember', function ($evaluation_sending_id) use ($app)
		{
			$post = $_SESSION['value'] = $app->request->post();

			$sendings = Sending::find_all_by_evaluation_sending_id($evaluation_sending_id);
			$evaluation_sending = EvaluationSending::find_by_id($evaluation_sending_id);
			$evaluation = $evaluation_sending->evaluation;

			if ( ! $sendings) {
				$app->flash('errors', array('O envio não tem nenhum funcionário'));
				return $app->redirect($app->request->getReferrer());
			}

			$post['text'] = strip_tags(trim($post['text']));

			if ($post['text'] == '') {
				$app->flash('errors', array('Preencha a mensagem'));
				return $app->redirect($app->request->getReferrer());
			}

			$input = array(
				'message' => $post['text'],
				'subject' => 'LEMBRETE - Avaliação',
				'replacements' => array(
					"#avaliacao" => $evaluation->name,
			    	"#inicio" => $evaluation->start_at->format('d/m/Y'),
			    	"#termino" => $evaluation->end_at->format('d/m/Y'),
				),
			);

			switch ($post['to']) {
				case 'all':

					foreach ($sendings as $sending) {

						$input['to']['name'] = $sending->valued->name;
						$input['to']['email'] = $sending->valued->email;

						$input['replacements']["#questionario"] = Sending::getUrlReply($sending->token);
						$input['replacements']["#nome"] = $sending->valued->name;

						$mail = new Mail($input);
						$send = $mail->send();

						if (is_array($send)) {
							$app->flash('errors', $send['error']);
							return $app->redirect($app->request->getReferrer());
						}
					}

					break;
				case 'not_answered':
					
					foreach ($sendings as $sending) {

						if ($sending->status > 1)
							continue;

						$input['to']['name'] = $sending->valued->name;
						$input['to']['email'] = $sending->valued->email;

						$input['replacements']["#questionario"] = Sending::getUrlReply($sending->token);
						$input['replacements']["#nome"] = $sending->valued->name;

						$mail = new Mail($input);
						$send = $mail->send();

						if (is_array($send)) {
							$app->flash('errors', $send['error']);
							return $app->redirect($app->request->getReferrer());
						}
					}

					break;
				default:

					if ( ! isset($post['valued_id'])) {
						$app->flash('errors', array('Para quais avaliados você quer enviar?'));
						return $app->redirect($app->request->getReferrer());
					}
					
					$sendings = Sending::all(array(
						'conditions' => array(
							'evaluation_sending_id =? and valued_id in (?)',
							$evaluation_sending_id,
							$post['valued_id']
						),
					));

					foreach ($sendings as $sending) {

						if ($sending->status > 1)
							continue;

						$input['to']['name'] = $sending->valued->name;
						$input['to']['email'] = $sending->valued->email;

						$input['replacements']["#questionario"] = Sending::getUrlReply($sending->token);
						$input['replacements']["#nome"] = $sending->valued->name;

						$mail = new Mail($input);
						$send = $mail->send();

						if (is_array($send)) {
							$app->flash('errors', $send['error']);
							return $app->redirect($app->request->getReferrer());
						}
					}

					break;
			}

			

			$app->flash('success', array('Lembrete enviado'));
			return $app->redirect($app->request->getReferrer());
		});

		$app->get('/delete', function ($sending_id) use ($app)
		{
			$sending = Sending::find_by_id($sending_id);
			if ($sending)
				$sending->delete();

			return $app->redirect($app->request->getReferrer());

		})->name('sending_delete');

		$app->get('/sending', function ($sending_id) use ($app)
		{
			$sending = Sending::find_by_id($sending_id);
			$sending_sendings = $sending->sending_sendings;

			$view = array(
				'title' => $sending->name,
				'sendings' => $sending_sendings,
				'sending' => $sending
			);

			return $app->render('admin/sending/sending.html.twig', $view);

		})->name('sending_delete');

		$app->get('/send', function ($sending_id) use ($app)
		{
			$sending_sending = sendingSending::create(array(
				'sending_id' => $sending_id
			));

			$sending = Sending::find_by_id($sending_id);
			$sendings = $sending->sent($sending_sending);

			if ($sendings) {
				
			}

			return $app->redirect($app->settings['urlbase_adm'] . '/sending/'.$sending_id.'/sending');

		})->name('sending_delete');

		$app->map('/edit', function ($sending_id) use ($app)
		{
			$sending = Sending::find_by_id($sending_id);

			if ($app->request->isPost()) {

				$post = $app->request->post();
				
				$post['start_at'] = $post['start_at_date'].' '.$post['start_at_time'];
				$post['end_at'] = $post['end_at_date'].' '.$post['end_at_time'];

				$mail_co = User::mailIsValid($post['mail_co']);

				$post['mail_co'] = implode(', ', $mail_co['valid']);

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

				$update = $sending->update_attributes($post);

				if ($save) {
					return $app->redirect($app->request->getReferrer());
				}

				return $app->redirect($app->settings['urlbase_adm'].'/sending/'.$sending->id);
			}

			$evaluators = User::all(array(
				'conditions' => array('profile_type=?', 'appraiser')
				)
			);

			$view = array(
				'title' => 'Editando '.$sending->name,
				'sending' => $sending,
				'questionnaires' => check_sending_questionnaires(Questionnaire::all(), $sending),
				'groups' => check_sending_groups(Group::all(), $sending),
				'evaluators' => check_sending_evaluators($evaluators, $sending)
			);

			return $app->render('admin/sending/edit.html.twig', $view);

		})->via('GET', 'POST')->name('sending_edit');
	});

	$app->map('/new(/copy/:sending_id)', function ($copy = false) use ($app)
	{
		$view = array(
			'title' => 'Criar Avaliação',
			'copy' => $copy,
			'sendings' => Sending::all()
		);

		if ($app->request->isPost()) {
			
			$post = $app->request->post();
			
			$user_id = User::getUserLogger('id');
			$action = isset($post['action']) ? $post['action'] : 'new';

			switch ($action) {
				case 'new':

					$mail_co = User::mailIsValid($post['mail_co']);
					$start_at = joinDateTime($post['start_at_date'], $post['start_at_time']);
					$end_at = joinDateTime($post['end_at_date'], $post['end_at_time']);

					$sending = Sending::create(array(
						'user_id' => $user_id,
						'name' => $post['name'],
						'subject' => $post['subject'],
						'start_at' => $start_at,
						'end_at' => $end_at,
						'message_email' => $post['message_email'],
						'mail_co' => implode(', ', $mail_co['valid']),
						'status' => 0
					));
					break;

				case 'copy':
					
					if ( $post['sending_id'] == '') {
						die('sending_id');
					}

					$start_at = joinDateTime($post['start_at_date'], $post['start_at_time']);
					$end_at = joinDateTime($post['end_at_date'], $post['end_at_time']);

					$find = Sending::find_by_id($post['sending_id']);
					$sending = $find->copy(array(
						'name' => $post['name_copy'],
						'user_id' => $user_id,
						'start_at' => $start_at,
						'end_at' => $end_at
					));

					break;
				
				default:
					# code...
					break;
			}

			if ($sending->is_invalid()) {
				dd($sending->errors->full_messages());
			}

			return $app->redirect($app->settings['urlbase_adm'] . '/sending/'.$sending->id.'/edit');
		}

		return $app->render('admin/sending/new.html.twig', $view);
		
	})->via('GET', 'POST')->name('sending_new');
});