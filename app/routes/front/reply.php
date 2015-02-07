<?php

$app->group('/reply', function () use ($app)
{
	$app->post('/', function () use ($app)
	{
		$post = $app->request->post();

		$questionnaire_id = $post['questionnaire_id'];
		$token = $post['token'];

		$sending = Sending::find_by_token(Sending::decodeToken($token));
		$evaluation = $sending->evaluation_sending->evaluation;

		if ( ! $sending)
			return $app->notfound();

		if ( $evaluation->getStatus() == 'Expirada') {
				return $app->redirect(config('domain').'reply/'.$token.'/expired');
			}

		$attributes = Answer::prepare($post);

		$insert = Answer::uniqueness( $attributes, $sending->valued_id, $sending->evaluation_sending_id);

		if ($insert !== true) {
			$app->flash('errors', $insert);
			return $app->redirect(config('domain').'reply/'.$token);
		}
			

		$is_finalized = isset($post['finalize']);

		if ($is_finalized) {

			setcookie('note_sum_1_10', '', time()-3600, '/');
			
			$update = $sending->update_attributes(array(
				'status' => 3,
				'answered_at' => date('Y-m-d H:i:s')
			));

			$evaluation->update_attributes(array('status' => 3));

			return $app->redirect(config('domain').'reply/'.$token.'/finalized');		
		}

		$status = array('status' => 2);

		$update = $sending->update_attributes($status);
		$evaluation->update_attributes($status);

		return $app->redirect(config('domain').'reply/'.$token.'/questionnaire/'.($post['key'] + 1));
	});

	$app->group('/:token', function ($token = null) use ($app)
	{
		$app->get('(/questionnaire/:questionnaire_key)', function ($token, $questionnaire_key = 0) use ($app)
		{
			$sending = Sending::find_by_token(Sending::decodeToken($token));
			$evaluation = $sending->evaluation_sending->evaluation;

			if ( ! $sending)
				return $app->notfound();

			if ($sending->status > 2 )
				return $app->redirect(config('domain').'reply/'.$token.'/finalized');

			if ( $evaluation->getStatus() == 'Expirada') {
				return $app->redirect(config('domain').'reply/'.$token.'/expired');
			}

			$sending->update_attributes(array(
				'status' => ($questionnaire_key == 0 ? 1 : 2)
			));

			if ($questionnaire_key == 0) {
				setcookie('note_sum_1_10', '', -3600, '/');
			}

			if ($sending->status == 1) {
				$sending->update_attributes(array(
					'viewed_at' => date('Y-m-d H:i:s')
				));
			}


			if ( ! isset($evaluation->questionnaires[$questionnaire_key]))
				return $app->notfound();

			$questionnaire_current = $evaluation->questionnaires[$questionnaire_key];
			$next = isset($evaluation->questionnaires[$questionnaire_key + 1]) ? true : false;

			$issues = $questionnaire_current->issues;

			// foreach ($issues as $key => $issue) {
				
			// 	$attributes = array(
			// 		'conditions' => array(
			// 			'issue_id=? AND valued_id=? AND evaluation_id=?',
			// 			$issue->id, 
			// 			$sending->valued->id,
			// 			$evaluation->id
			// 		)
			// 	);

			// 	$check_issues = Answer::all($attributes);

			// 	if ($check_issues)
			// 		unset($issues[$key]);
			// }

			if (empty($issues) AND $next)
				return $app->redirect(config('domain').'reply/'.$token.'/questionnaire/'.($questionnaire_key + 1));

			$view = array(
				'evaluation' => $evaluation,
				'valued' => $sending->valued,
				'token' => $token,
				'key' => $questionnaire_key,
				'questionnaire' => $questionnaire_current,
				'questionnaires' => $evaluation->questionnaires,
				'issues' => $issues,
				'next' => $next,
			);


			if ($view['next'])
				$view['questionnaire_next'] = $evaluation->questionnaires[$questionnaire_key + 1];

			return $app->render('front/reply.html.twig', $view);
		});

		$app->group('/finalized', function ($token = null) use ($app)
		{
			$app->get('/', function ($token) use ($app)
			{
				$sending = Sending::find_by_token(Sending::decodeToken($token));

				if ( ! $sending)
					return $app->notfound();

				if ($sending->status < 3)
					return $app->redirect(config('domain').'reply/'.$token);

				$evaluation = $sending->evaluation_sending->evaluation;

				$pdf = Pdf::find_by_sending_id_and_is_available($sending->id, 1);
				$data = Correction::bySending($sending->id);

				$view = array(
					'evaluation' => $evaluation,
					'valued' => $sending->valued,
					'sending' => $sending,
					'token' => $token,
					'data' => $data,
					'questionnaires' => $evaluation->questionnaires
				);

				if ($pdf) {

					$view['evaluator'] = $evaluator = User::find_by_id($pdf->evaluator_id);
					$view['evaluator_id'] = $evaluator->id;
					$view['pdf'] = $pdf;
					$view['layout'] = 'print';

					return $app->render('admin/sending/pdf_print.html.twig', $view);
				}
				
				return $app->render('front/finalized.html.twig', $view);
			});
		});

		$app->group('/expired', function ($token = null) use ($app)
		{
			$app->get('/', function ($token) use ($app)
			{
				$sending = Sending::find_by_token(Sending::decodeToken($token));

				if ( ! $sending)
					return $app->notfound();

				$evaluation = $sending->evaluation_sending->evaluation;

				$view = array(
					'evaluation' => $evaluation,
					'valued' => $sending->valued,
					'sending' => $sending,
					'token' => $token,
					'questionnaires' => $evaluation->questionnaires
				);
				
				return $app->render('front/expired.html.twig', $view);
			});
		});

		$app->get('/print', function ($token) use ($app)
		{
			$sending = Sending::find_by_token(Sending::decodeToken($token));

			if ( ! $sending)
				return $app->notfound();

			$evaluation = $sending->evaluation_sending->evaluation;

			$valued = User::find_by_id($sending->valued_id);
			$answers = Answer::find_all_by_valued_id_and_evaluation_sending_id($sending->valued_id, $sending->evaluation_sending_id);

			$view = array(
				'title' => 'Respostas',
				'answers' => $answers,
				'sending' => $sending,
				'evaluate' => false,
				'evaluation' => $evaluation,
				'valued' => $valued,
				'sending_id' => $sending->id,
				'token' => $token
			);

			return $app->render('front/answers_print.html.twig', $view);
		});

		// $app->get('/correction', function ($token) use ($app)
		// {
		// 	$sending = Sending::find_by_token(Sending::decodeToken($token));

		// 	if ( ! $sending)
		// 		return $app->notfound();

		// 	if ($sending->status < 5)
		// 		return $app->redirect(config('domain').'reply/'.$token.'/finalized');

		// 	$evaluation = $sending->evaluation_sending->evaluation;

		// 	$corrections = Correction::getByValuedId($sending->valued_id);
			
		// 	$view = array(
		// 		'corrections' => $corrections,
		// 		'valued' => $sending->valued,
		// 		'evaluation' => $evaluation,
		// 	);

		// 	return $app->render('front/correction.html.twig', $view);
		// });
	});
});