<?php
function config($param)
{
	$paramns = array(
		'prod' => array(

			'mail' => array(
				'smtp' => 'smtp.peoplehub.com.br',
				'port' => 25,
				'email' => 'site@peoplehub.com.br',
				'name' => 'Avaliacao',
				'pass' => 'NovaSenha14',
			),

			'domain' => 'http://www.freitasleite.com.br/avaliacao/',

			'db' => array(
				'driver' => 'mysql',
				'host' => 'localhost',
				'user' => 'freitasleite',
				'pass' => 'FladVtMp30',
				'base' => 'freitasleite',
			)
		),

		'dev' => array(

			'mail' => array(
				'smtp' => 'smtp.live.com',
				'port' => 25,
				'email' => 'gilglecio_dev@hotmail.com',
				'name' => 'Gilglécio Santos',
				'pass' => '1gil2glecio3dev',
			),

			'domain' => 'http://localhost:8000/',

			'db' => array(
				'driver' => 'mysql',
				'host' => 'localhost',
				'user' => 'root',
				'pass' => '123',
				'base' => 'avaliacao2',
			)
		)
	);

	$paramns = $paramns[ENV_DEFAULT];

	$split = explode('.', $param);

	switch (count($split)) {
		case 1: return $paramns[$split[0]]; break;
		case 2: return $paramns[$split[0]][$split[1]]; break;
		case 3: return $paramns[$split[0]][$split[1]][$split[2]]; break;
		case 4: return $paramns[$split[0]][$split[1]][$split[2]][$split[3]]; break;
		default: return $paramns; break;
	}
}

function join_e($list, $separator = ', ', $and = ' e ')
{
	$input = $list;
	$end = array_pop($list);

	switch (count($input)) {
		case 0: return ''; break;
		case 1: return $input[0]; break;
		
		default:
			return implode($separator, $list).$and.$end;
			break;
	}
}

function mes($m)
{
	$months = array(
		'Janeiro',
		'Fevereiro',
		'Março',
		'Abril',
		'Maio',
		'Junho',
		'Julho',
		'Agosto',
		'Setembro',
		'Outubro',
		'Novembro',
		'Dezembro',
	);
}

function full_date($date)
{
	$week_days = array(
		'Domingo',
		'Segunda-Feira',
		'Terça-Feira',
		'Quarta-Feira',
		'Quinta-Feira',
		'Sexta-Feira',
		'Sabado'
	);

	return $week_days[date('w', strtotime($date))].', '.date('d M Y à\s H:i');
}
function dateBRtoUS($date)
{
	if (strlen($date) != 10)
		return null;

	return implode('-', array_reverse(explode('/', $date)));
}
function joinDateTime($date, $time)
{
	if (strpos($date, '/')) {
		$date = dateBRtoUS($date);
	}

	return $date.' '.$time;
}
function date_db($date)
{
	if ( ! $date) {
		return null;
	}
	return implode('-', array_reverse(explode('/', $date)));
}
function money($money)
{
	return str_replace(',', '.', str_replace('.', '', $money));
}
function set_user_charge($charges_id, $user)
{
	foreach ($charges_id as $charge_id) {

		$find = UserCharge::find(array(
			'conditions' => array(
				'user_id=? AND charge_id=?',
				$user->id, $charge_id
			)
		));

		if ( ! $find ) {
			$UserCharge = UserCharge::create(array(
				'user_id' => $user->id,
				'charge_id' => $charge_id
			));

			if ( $UserCharge->is_invalid() ) {
				
			}
		}		
	}

	return true;
}
function set_user_rating($ratings_id, $user)
{
	foreach ($ratings_id as $rating_id) {

		$find = UserRating::find(array(
			'conditions' => array(
				'user_id=? AND rating_id=?',
				$user->id, $rating_id
			)
		));

		if ( ! $find ) {
			$UserRating = UserRating::create(array(
				'user_id' => $user->id,
				'rating_id' => $rating_id
			));

			if ( $UserRating->is_invalid() ) {
				
			}
		}		
	}

	return true;
}
function split_charges($charges)
{
	$charges_id = array();
	$charges = array_filter(explode(',', $charges));
	
	$errors = array();

	foreach ($charges as $charge) {

		$charge = trim($charge);

		$find = Charge::find_by_name($charge);

		if ($find) {
			array_push($charges_id, $find->id);
		} else {
			$create = Charge::create(array(
				'name' => strip_tags(trim($charge))
			));
			if ($create->is_valid()) {
				array_push($charges_id, $create->id);
			} else {
				array_push($errors, $create->errors->full_messages());
			}
		}
	}
	return $charges_id;		
}
function split_ratings($ratings)
{
	$ratings_id = array();
	$ratings = array_filter(explode(',', $ratings));
	
	$errors = array();
	
	foreach ($ratings as $rating) {
		$rating = trim($rating);
		$find = Rating::find_by_name($rating);

		if ($find) {
			array_push($ratings_id, $find->id);
		} else {

			$create = Rating::create(array(
				'name' => strip_tags(trim($rating))
			));
			if ($create->is_valid()) {
				array_push($ratings_id, $create->id);
			} else {
				array_push($errors, $create->errors->full_messages());
			}
		}
	}
	return $ratings_id;
}
function check_evaluation_questionnaires($questionnaires, $evaluation = null)
{
	$data = array();

	foreach ($questionnaires as $questionnaire) {

		$data[$questionnaire->id] = array(
			'checked' => 0,
			'name' => $questionnaire->name,
			'name_private' => $questionnaire->name_private,
			'issues' => $questionnaire->questionnaire_issues,
		);
	}
	
	if ($evaluation) {
		foreach ($evaluation->evaluation_questionnaires as $questionnaire) {
			 $data[$questionnaire->questionnaire_id]['checked'] = 1;
		}
	} 

	return $data;
}
function check_evaluation_evaluators($evaluators, $evaluation = null)
{
	$data = array();

	foreach ($evaluators as $evaluator) {

		$data[$evaluator->id] = array(
			'checked' => 0,
			'name' => $evaluator->name
		);
	}
	
	if ($evaluation) {

		foreach ($evaluation->evaluation_evaluators as $evaluator) {
			 $data[$evaluator->evaluator_id]['checked'] = 1;
		}
	} 

	return $data;
}
function check_evaluation_groups($groups, $evaluation = null)
{
	$data = array();

	foreach ($groups as $group) {

		$data[$group->id] = array(
			'checked' => 0,
			'name' => $group->name
		);
	}
	
	if ($evaluation) {
		foreach ($evaluation->evaluation_groups as $group) {
			 $data[$group->group_id]['checked'] = 1;
		}
	} 

	return $data;
}
function check_groups($groups, $user = null)
{
	$data = array();

	foreach ($groups as $group) {

		$data[$group->id] = array(
			'checked' => 0,
			'name' => $group->name
		);
	}
	
	if ($user) {
		foreach ($user->user_groups as $group) {
			 $data[$group->group_id]['checked'] = 1;
		}
	} 

	return $data;
}
function check_charges($charges, $user = null)
{
	$data = array();

	foreach ($charges as $charge) {

		$data[$charge->id] = array(
			'checked' => 0,
			'name' => $charge->name
		);
	}
	
	if ($user) {
		foreach ($user->user_charges as $charge) {
			 $data[$charge->charge_id]['checked'] = 1;
		}
	} 

	return $data;
}
function check_ratings($ratings, $user = null)
{
	$data = array();

	foreach ($ratings as $rating) {

		$data[$rating->id] = array(
			'checked' => 0,
			'name' => $rating->name
		);
	}

	if ($user) {
		foreach ($user->user_ratings as $rating) {
			$data[$rating->rating_id]['checked'] = 1;
		}
	}

	return $data;
}
function to_select2($model, $id, $text)
{
	$data = array();

	foreach ($model as $v) {
		$d['id'] = $v->$id;
		$d['text'] = $v->$text;

		$data[] = $d;
	}

	return json_encode($data);
}
function slug($string, $slug = false) 
{
	$string = strtolower($string);
	$ascii['a'] = range(224, 230);
	$ascii['e'] = range(232, 235);
	$ascii['i'] = range(236, 239);
	$ascii['o'] = array_merge(range(242, 246), array(240, 248));
	$ascii['u'] = range(249, 252);
	$ascii['b'] = array(223);
	$ascii['c'] = array(231);
	$ascii['d'] = array(208);
	$ascii['n'] = array(241);
	$ascii['y'] = array(253, 255);

	foreach ($ascii as $key=>$item) {
		
		$acentos = '';

		foreach ($item AS $codigo) 
			$acentos .= chr($codigo);

		$troca[$key] = '/['.$acentos.']/i';
	}

	$string = preg_replace(array_values($troca), array_keys($troca), $string);

	if ($slug) {
		$string = preg_replace('/[^a-z0-9]/i', $slug, $string);
		$string = preg_replace('/' . $slug . '{2,}/i', $slug, $string);
		$string = trim($string, $slug);
	}

	return $string;
}

function response_json($errors)
{
	$errors = implode(', ', $errors);

	$response['text'] = $errors;

	return '?response='.json_encode($response);
}

function response(array $itens, $class = 'success')
{
	return implode('<br />', $itens);
}

function dd($input, $dump = false)
{
	echo "<pre>";
	if ($dump) {
		var_dump($input);
	} else {
		print_r($input);
	}
	echo "</pre>";

	exit;
}
?>