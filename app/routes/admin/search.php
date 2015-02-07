<?php

$app->group('/search', function () use ($app) {

	$app->get('/', function () use ($app)
	{
		$get = $app->request->get();
		$search = new Search($get, $app);
		$result = $search->search();

		$view = array(
			'title' => 'Lista',
			'result' => $result
		);

		$app->render('admin/search/list.html.twig', $view);
		
	});
});