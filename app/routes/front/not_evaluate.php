<?php

$app->group('/not_evaluate/:token', function ($token = null) use ($app)
{
	$app->get('/', function ($token = null) use ($app)
	{
		$sending_bcc = SendingBcc::find_by_token(Sending::decodeToken($token));

		if ( ! $sending_bcc)
			return $app->notfound();

		$sendings = $sending_bcc->evaluation_sending->sendings;
		$evaluation_sending = $sending_bcc->evaluation_sending;

		if ( ! $evaluation_sending)
			return $app->notfound();

		$evaluation = $sending_bcc->evaluation_sending->evaluation;

		$view = array(
			'title' => 'Lista de Avaliações',
			'sendings' => $sendings,
			'evaluation' => $evaluation,
			'token' => $token,
			'evaluator' => array(
				'name' => $sending_bcc->name,
				'email' => $sending_bcc->email
			)
		);

		return $app->render('front/correct_list_valued_bcc.html.twig', $view);
	});

	$app->get('/valued/:valued_id', function ($token = null, $valued_id = null) use ($app) 
	{
		$sending_bcc = SendingBcc::find_by_token(Sending::decodeToken($token));

		if ( ! $sending_bcc)
			return $app->notfound();

		$evaluation = $sending_bcc->evaluation_sending->evaluation;

		$valued = User::find_by_id($valued_id);
		$answers = Answer::find_all_by_valued_id_and_evaluation_sending_id($valued_id, $sending_bcc->evaluation_sending_id);
		$sending = Sending::find_by_valued_id_and_evaluation_sending_id($valued_id, $sending_bcc->evaluation_sending_id);

		$view = array(
			'title' => 'Avaliação: '.$evaluation->name.' - Funcionário: '.$valued->name,
			'answers' => $answers,
			'evaluate' => false,
			'evaluation' => $evaluation,
			'valued' => $valued,
			'sending_id' => $sending->id,
			'evaluator' => array(
				'name' => $sending_bcc->name,
				'email' => $sending_bcc->email
			),
			'token' => $token
		);

		return $app->render('front/correct_answer.html.twig', $view);
	});
});