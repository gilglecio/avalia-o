<?php

$app->get('/login', function () use ($app)
{
	$app->redirect(config('domain'));
});

$app->post('/reply/increment', function () use ($app)
{
	$post = $app->request->post();
	// unset($_SESSION['note_sum_1_10'], $_SESSION['reply_increment']);
	if ($post['note']) {
		setcookie('note_sum_1_10', '', time()-3600, '/');
		setcookie('note_sum_1_10', $post['note'], time()+3600, '/');
		//$_SESSION['note_sum_1_10'] = ;
		//$app->flash('note_sum_1_10', (int) $post['note']);
	}
	dd(array($post['note'], $_COOKIE));

	die(json_encode(array('sum' => $_SESSION['note_sum_1_10'])));
});

$app->get('/mail', function ()
{
	$mail = new Mail(array(
		'to' => array(
			'name' => 'Gilglécio',
			'email' => 'gilglecio_765@hotmail.com'
		),
		'message' => '<html>
		<head>
			<title>teste</title>
		</head>
		<body>
			<h1>Teste de envio de e-mails, obrigado pela atenção.</h1>
		</body>
		</html>',
		'subject' => 'Avaliação'
	));

	dd($mail->send());
});

$app->get('/logout', function () use ($app)
{
	unset($_SESSION['app.user_id']);
	return $app->redirect(config('domain'));
});

// $app->group('/questionnaire', function () use ($app) {

// 	$app->get('/:questionnaire_id', function ($questionnaire_id) use ($app)
// 	{
// 		$questionnaire = Questionnaire::find_by_id($questionnaire_id);

// 		$view = array(
// 			'questionnaire' => $questionnaire
// 		);

// 		return $app->render('front/questionnaire.html.twig', $view);
		
// 	});

// });

$app->map('/', function () use ($app)
{
	if ($app->request->isPost()) {
		
		$post = $app->request->post();

		$username = $post['username'];
		$password = $post['password'];

		$remenber_me = isset($post['remenber_me']) ? $post['remenber_me'] : null;

		$options = array(
			'conditions' => array(
				'username=? AND profile_type=?',
				$username,
				'admin'
			)
		);

		$users = User::all($options);

		if ( ! $users) {
			$app->flash('errors', array('Usuário não localizado.'));
			$app->redirect($app->request->getReferrer());
		}

		if ($users) {

			foreach ($users as $user) {
				if (crypt($password, $user->password) == $user->password) {

					$_SESSION['app.user_id'] = $user->id;
					break;
				}
			}

			if ( ! isset($_SESSION['app.user_id'])) {
				$app->flash('errors', array('Senha Incorreta.'));
				$app->flash('username', $username);
				return $app->redirect(config('domain'));
			}

			if (isset($_SESSION['before'])) {
				$before = $_SESSION['before'];
				unset($_SESSION['before']);
				// dd($before);
				return $app->redirect('http://'.$_SERVER['HTTP_HOST'].$before);
			}

			return $app->redirect($app->settings['urlbase_adm']);
		}
	}

	return $app->render('front/login.html.twig');
	
})->via('GET', 'POST');

include 'front/reply.php';
include 'front/correct.php';
include 'front/not_evaluate.php';