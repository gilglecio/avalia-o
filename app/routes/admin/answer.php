<?php

$app->group('/answer', function () use ($app) {

	$app->get('/', function () use ($app)
	{
		$evaluations = Evaluation::all();	

		$view = array(
			'title' => 'Avaliaçãoes',
			'evaluations' => $evaluations
		);

		$app->render('admin/answer/evaluation.html.twig', $view);
		
	});

	$app->get('/evaluation/:evaluation_id', function ($evaluation_id = null) use ($app)
	{
		$evaluation_sendings = EvaluationSending::find_all_by_evaluation_id($evaluation_id);
		$evaluation = Evaluation::find_by_id($evaluation_id);

		if ( ! $evaluation)
			return $app->notfound();

		$view = array(
			'title' => $evaluation->name,
			'evaluation' => $evaluation,
			'evaluation_sendings' => $evaluation_sendings
		);

		$app->render('admin/answer/evaluation_sending.html.twig', $view);
		
	});

	$app->get('/evaluation/:evaluation_id/sending/:evaluation_sending_id', function ($evaluation_id = null, $evaluation_sending_id = null) use ($app)
	{
		$sendings = Sending::find_all_by_evaluation_sending_id($evaluation_sending_id);
		$evaluation = Evaluation::find_by_id($evaluation_id);

		if ( ! $evaluation)
			return $app->notfound();

		$view = array(
			'title' => $evaluation->name,
			'evaluation' => $evaluation,
			'sendings' => $sendings
		);

		$app->render('admin/answer/sending.html.twig', $view);
		
	});
});