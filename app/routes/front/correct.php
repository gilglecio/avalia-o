<?php

$app->group('/correct/:token', function ($token = null) use ($app)
{
	$app->map('/', function ($token) use ($app)
	{
		$sending_evaluator = SendingEvaluator::find_by_token(Sending::decodeToken($token));

		if ( ! $sending_evaluator)
			return $app->notfound();

		$evaluator = $sending_evaluator->evaluator;

		if ($app->request->isPost()) {
			
			$post = $app->request->post();

			if ( ! isset($post['sending_id']))
				return $app->notfound();

			$attributes = Correction::prepare($post);
			$correction = Correction::uniqueness($attributes, $evaluator->id);

			$sending = Sending::find_by_id($post['sending_id']);

			$evaluation = $sending->evaluation_sending->evaluation;

			$attributes = array('status' => 5);

			if ( is_array($correction)) {
				$app->flash('errors', $correction);
				$attributes = array('status' => 4);
				$evaluation->update_attributes(array('status' => 4));
			}
			
			$sending->update_attributes($attributes);
			$sending->update_attributes(array(
				'corrected_at' => date('Y-m-d H:i:s')
			));

			$evaluation->update_attributes(array('status' => 5));

			$app->redirect( config('domain').'correct/'.$token);
		}

		$evaluation = $sending_evaluator->evaluation_sending->evaluation;
		$sendings = $sending_evaluator->evaluation_sending->sendings;

		$view = array(
			'title' => 'Lista de Avaliações',
			'sendings' => $sendings,
			'evaluator' => $evaluator,
			'evaluation' => $evaluation,
			'token' => $token
		);

		return $app->render('front/correct_list_valued.html.twig', $view);

	})->via('GET', 'POST');

	$app->map('/valued/:valued_id/sending/:sending_id', function ($token, $valued_id, $sending_id) use ($app)
	{
		$sending_evaluator = SendingEvaluator::find_by_token(Sending::decodeToken($token));

		if ( ! $sending_evaluator)
			return $app->notfound();

		$evaluator = $sending_evaluator->evaluator;
		$evaluation = $sending_evaluator->evaluation_sending->evaluation;

		$valued = User::find_by_id($valued_id);

		$answers = Answer::find_all_by_valued_id_and_evaluation_sending_id($valued_id, $sending_evaluator->evaluation_sending_id);
		$sending = Sending::find_by_id($sending_id);

		$get = $app->request->get();

		$print = isset($get['print']) ? $get['print'] : false;

		if ($sending->isCorrectedByEvaluator($evaluator->id) OR $print) {

			$data = Correction::bySending($sending_id);

			if ( ! $sending) {
				return $app->notfound();
			}

			$evaluator_id = $evaluator->id;

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
				'title' => '',
				'print_questionnaire' => $print,
				'data' => $data,
				'token' => $token,
				'sending_id' => $sending_id,
				'sending' => $sending,
				'valued' => $valued,
				'evaluator' => $evaluator,
				'evaluation' => $evaluation,
				'evaluator_id' => $evaluator_id
			);

			return $app->render('front/correction.html.twig', $view);

			// $app->flash('errors', array('Você ja corrigiu esta avaliação.'));
			// $app->redirect(config('domain').'correct/'.$token);
		}

		$sum_issue_0_10 = 0;

		foreach ($answers as $answer) {
			if ($answer->issue->type == '0-10') {
				$sum_issue_0_10 += $answer->answer;
			}
		}

		$view = array(
			'title' => 'Correção',
			'answers' => $answers,
			'evaluate' => true,
			'sending' => $sending,
			'evaluation' => $evaluation,
			'valued' => $valued,
			'sum_issue_0_10' => number_format($sum_issue_0_10, 2, ',', '.'),
			'sending_id' => $sending_id,
			'print' => $print,
			'evaluator' => $evaluator,
			'token' => $token
		);

		return $app->render('front/correct_answer.html.twig', $view);

	})->via('GET', 'POST');
});