<?php



$app->hook('slim.after.dispatch', function () use ($app) {
    $evaluations = Evaluation::all(array(
        'conditions' => array(
            'start_at < ? AND end_at > ? AND is_alerted = ?',
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
            0
        ),
    ));

    $questionnaires = Questionnaire::all();
    foreach ($questionnaires as $questionnaire) {
        if ($questionnaire->name_private != '') {
            continue;
        }
        
        $questionnaire->update_attributes(array(
            'name_private' => $questionnaire->name
        ));
    }

    $message = array();

    // foreach ($evaluations as $evaluation) {
    // 	if (count($evaluation->evaluation_sendings) == 0) {
    // 		$evaluation->update_attributes(array(
    // 			'is_alerted' => 1
    // 		));
    // 		array_push($message, $evaluation->name.' '.$evaluation->getStatus().' (Início '.date('d/m/Y H:i', strtotime($evaluation->start_at)).') Enviar? '.$app->settings['urlbase_adm'].'/evaluation/'.$evaluation->id.'/send');
    // 	}
    // }

    // if ( ! empty($message)) {
    // 	$message = implode("\r\n", $message);
    // 	$send = Evaluation::send_mail_admins('Avalicoes que precisam ser envidas', $message);
    // }
});

$app->hook('slim.before.dispatch', function () use ($app) {
    $get = $app->request->get();

    if (isset($get['response'])) {
        $app->view->appendData(array(
            'response' => json_decode(substr($get['response'], 0, strpos($get['response'], '}')+1))
        ));
    }
    $app->view->appendData(array(
        'note_sum_1_10' => (isset($_COOKIE['note_sum_1_10']) ? $_COOKIE['note_sum_1_10'] : 0)
    ));

    if (isset($_SESSION['slim.flash']['errors'])) {
        $app->view->appendData(array('response' => array(
            'text' => is_array($_SESSION['slim.flash']['errors']) ? '<p>- '.implode('</p><p>- ', $_SESSION['slim.flash']['errors']).'</p>' : $_SESSION['slim.flash']['errors']
        )));
    }

    if (isset($_SESSION['value'])) {
        $app->view->appendData(array(
            'value' => $_SESSION['value']
        ));

        unset($_SESSION['value']);
    }

    if (isset($_SESSION['slim.flash']['success'])) {
        $app->view->appendData(array('response' => array(
            'text' => '- '.implode('<br />- ', $_SESSION['slim.flash']['success']),
            'class' => 'success'
        )));
    }

    $self = Setting::find('last');

    $site = array(
        'site_name' => 'avaliacao',
        'site_email' => 'avaliacao@hotmail.com',
        'site_src_logo' => config('domain').'static/img/logo-avaliacao.gif'
    );

    if ($self) {
        $site['site_name'] = $self->getName();
        $site['site_email'] = $self->getEmail();

        $site['site_src_logo'] = $self->getSrcLogo();
        $site['site_description'] = $self->getDescription();
        $site['site_status'] = $self->getStatus();
    }

    $app->view->appendData($site);
});
