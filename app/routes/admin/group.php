<?php

$app->group('/group', function () use ($app) {

	$app->get('/', function () use ($app)
	{
		$groups = Group::all(array(
			'include' => array('group_members'),
			'order' => 'name ASC'
		));

		return $app->render('admin/group/list.html.twig', array(
			'title' => 'Lista',
			'groups' => $groups
		));
	})->name('group_list');

	$app->group('/:group_id', function ($group_id = null) use ($app)
	{
		$app->get('/', function ($group_id) use ($app)
		{
			$app->redirect($app->settings['urlbase_adm'] . '/group/'.$group_id.'/edit/members');
		})->name('group_find');

		$app->get('/delete', function ($group_id) use ($app)
		{
			$delete = GroupMember::transaction( function () use ($group_id) {

				GroupMember::table()->delete(array(
					'group_id' => array($group_id)
				));

				$group = Group::find_by_id($group_id);
				if ($group) {
					$group->delete();
				}
			});

			if ($delete) {
				$app->redirect($app->request->getReferrer());
			}
		})->name('group_delete');

		$app->group('/edit', function () use ($app)
		{
			$app->map('/', function ($group_id) use ($app)
			{
				$group = Group::find_by_id($group_id);

				$view = array(
					'title' => 'Editando '.$group->name,
					'group' => $group
				);

				if ($app->request->isPost()) {

					$post = $view['value'] = $app->request->post();
					$group->name = $post['name'];
					$save = $group->save();

					if ( ! $save) {
						$app->flash('errors', array_values($group->errors->full_messages()));
					}

					$add_members = isset($post['add_member']) ? true : false;

					$app->flash('success', array('Salvo'));

					if ($add_members) {
						return $app->redirect($app->settings['urlbase_adm'] . '/group/' . $group->id . '/edit/members');
					}

					return $app->redirect($app->settings['urlbase_adm'] . '/group');
				}

				return $app->render('admin/group/edit.html.twig', $view);

			})->via('POST', 'GET')->name('group_edit');

			$app->map('/members', function ($group_id) use ($app)
			{
				$group = Group::find_by_id($group_id, array('include' => array('members')));

				if (! $group)
					return $app->notfound();

				if ($app->request->isPost()) {
					
					$post = $app->request->post();

					$action = $post['action'];
					$group_id = $post['group_id'];
					$member_id = $post['member_id'];

					$options = array(
						'user_id' => $member_id,
						'group_id' => $group_id
					);

					switch ($action) {

						case 'add':
							$create = GroupMember::create($options);
							die(GroupMember::count(array('conditions' => array('group_id=?', $group_id))));
							break;

						case 'rm':
							GroupMember::table()->delete($options);
							die(GroupMember::count(array('conditions' => array('group_id=?', $group_id))));
							break;
						
						default:
							die(json_encode(false));
							break;
					}

				}

				$group_members = $group->group_members;
				$valueds = User::find_all_by_profile_type('valued');

				$checkboxes = array();

				foreach ($valueds as $valued) {
					$l['checked'] = 0;
					$l['data']['name'] = $valued->name;

					$checkboxes[$valued->id] = $l;
				}

				foreach ($group_members as $member)
					$checkboxes[$member->user_id]['checked'] = 1;



				return $app->render('admin/group/edit_members.html.twig', array(
					'title' => 'Editar Membros',
					'group' => $group,
					'members' => $group_members,
					'checkboxes' => $checkboxes
				));

			})->via('GET', 'POST');

			$app->post('/evaluations', function ($group_id) use ($app)
			{
				$group = Group::find_by_id($group_id);
					
				$post = $app->request->post();

				$action = $post['action'];
				$group_id = $post['group_id'];
				$evaluation_id = $post['evaluation_id'];

				$options = array(
					'evaluation_id' => $evaluation_id,
					'group_id' => $group_id
				);

				switch ($action) {

					case 'add':
						$create = EvaluationGroup::create($options);
						die(EvaluationGroup::count(array('conditions' => array('group_id=?', $group_id))));
						break;

					case 'rm':
						EvaluationGroup::table()->delete($options);
						die(EvaluationGroup::count(array('conditions' => array('group_id=?', $group_id))));
						break;
					
					default:
						die(json_encode(false));
						break;
				}
			});
		});
	});

	$app->map('/new', function () use ($app)
	{
		$view = array(
			'title' => 'Criar Novo Grupo'
		);

		$post = $view['value'] = $app->request->post();

		if ($app->request->isAjax()) {

			$names = isset($post['name']) ? strip_tags(trim($post['name'])) : '';
			$split = array_filter(explode(',', $names));

			$groups = array();
			$errors = array();

			foreach ($split as $name) {
				
				$name = trim($name);

				$group = Group::create(array(
					'name' => $name
				));

				if ($group->is_invalid()) {
					array_push($errors, $name . ' já estava cadastrado.');
				} else {
					array_push($groups, array(
						'id' => $group->id,
						'name' => $group->name
					));
				}		
			}

			$json['groups'] = $groups;
			$json['errors'] = null;

			if (! empty($errors)) {
				$json['errors'] = $errors;
			}

			die(json_encode($json));
		}

		if ($app->request->isPost()) {

			if (strlen(trim($post['name'])) < 3) {
				$app->flash('errors', array('Informe o nome do Grupo.'));
				$app->redirect($app->request->getReferrer());
			}

			$group = Group::create(array(
				'name' => $post['name']
			));

			if ($group->is_invalid()) {
				$app->flash('errors', $group->errors->full_messages());
				$app->redirect($app->request->getReferrer());
			}

			if (isset($post['add_member'])) {
				return $app->redirect($app->settings['urlbase_adm'] . '/group/' . $group->id . '/edit/members');
			}

			return $app->redirect($app->settings['urlbase_adm'] . '/group');
		}

		if (isset($errors)) {
			$r['text'] = response($errors);
			$r['class'] = 'alert';

			$view['response'] = $r;
		}

		return $app->render('admin/group/new.html.twig', $view);

	})->via('POST')->name('group_new');
});
?>