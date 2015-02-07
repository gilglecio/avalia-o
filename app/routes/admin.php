<?php 

$app->group( $app->settings['urladm'] , $authenticate, function () use ($app) {

	$app->get('/', function () use ($app)
	{
		return $app->render('admin/home.html.twig');
	});

	include 'admin/group.php';
	include 'admin/user.php';
	include 'admin/rating.php';
	include 'admin/charge.php';
	include 'admin/questionnaire.php';
	include 'admin/issue.php';
	include 'admin/evaluation.php';
	include 'admin/sending.php';
	include 'admin/answer.php';
	include 'admin/settings.php';
	include 'admin/search.php';

	$app->get('/dev/sending', function () use ($app)
	{
		$sendings = Sending::all();
		$sending_evaluators = SendingEvaluator::all();
		$sending_bcc = SendingBcc::all();
		
		$app->render('admin/dev.sending.html.twig', array(
			'sendings' => $sendings,
			'sending_bcc' => $sending_bcc,
			'sending_evaluators' => $sending_evaluators
		));
	});

	$app->get('/mailto', function () use ($app)
	{
		return $app->render('admin/');
	});
});