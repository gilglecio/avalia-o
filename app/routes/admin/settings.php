<?php

$app->group('/settings', function () use ($app) {

	$app->map('/', function () use ($app)
	{
		$setting = Setting::find('last');

		if ($app->request->isPost()) {
			
			$post = $app->request->post();

			unset($post['csrf_token']);
			
			if ($setting) {
				$setting->update_attributes($post);
			} else {
				$setting = Setting::create($post);
			}

			$app->flash('success', array('Configurações Atualizadas'));

			return $app->redirect($app->request->getReferrer());
		}

		$view = array(
			'title' => 'Geral',
			'setting' => $setting
		);

		return $app->render('admin/settings/edit.html.twig', $view);

	})->via('GET', 'POST');

	$app->map('/change_password', function () use ($app)
	{
		if ($app->request->isPost()) {
			$post = $app->request->post();

			$new_password = isset($post['new_password']) ? trim($post['new_password']) : null;

			if ($new_password and strlen($new_password) > 3) {
				
				$user_logger_id = User::getUserLogger('id');

				if ( ! $user_logger_id)
					return $app->redirect(config('domain').'/logout');

				$user = User::find_by_id($user_logger_id);

				$update = $user->update_attribute('password', User::crypt_password($new_password));

				$app->flash('success', array('Senha atualizada com sucesso!'));

			} else {
				$app->flash('errors', array('Senha não informada ou é muito fraca.'));
			}

			$app->redirect($app->request->getReferrer());
		}

		return $app->render('admin/settings/change_password.html.twig', array(
			'title' => 'Alteração de senha'
		));
	})->via('GET', 'POST');

	$app->map('/change_email', function () use ($app)
	{
		if ($app->request->isPost()) {

			$post = $app->request->post();

			$new_email = isset($post['new_email']) ? trim($post['new_email']) : null;
			
			if ($new_email and strlen($new_email) > 3) {
				
				$user_logger_id = User::getUserLogger('id');

				if ( ! $user_logger_id)
					return $app->redirect(config('domain').'/logout');

				$user = User::find_by_id($user_logger_id);

				$user->update_attributes(array(
					'password' => User::crypt_email($new_email)
				));

				$app->flash('success', array('Senha atualizada com sucesso!'));

			} else {
				$app->flash('errors', array('Senha não informada ou é muito fraca.'));
			}

			$app->redirect($app->request->getReferrer());
		}

		return $app->render('admin/settings/change_email.html.twig', array(
			'title' => 'Alteração de senha'
		));
	})->via('GET', 'POST');
});