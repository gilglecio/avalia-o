<?php

class Evaluation extends Model
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var \Datetime
     */
    protected $start_at;

    /**
     * @var \Datetime
     */
    protected $end_at;

    /**
     * @var \Datetime
     */
    protected $created_at;

    /**
     * @var \Datetime
     */
    protected $updated_at;

    /**
     * @var string
     */
    protected $message_email;

    /**
     * @var string
     */
    protected $mail_bcc;

    /**
     * @var bool
     */
    protected $is_alerted;

    public static $has_many = array(
        array('evaluation_groups'),
        array('groups', 'through' => 'evaluation_groups'),
        array('evaluation_evaluators'),

        array('evaluation_questionnaires'),
        array('questionnaires', 'through' => 'evaluation_questionnaires'),

        array('evaluation_sendings'),
        array('sendings', 'through' => 'evaluation_sendings'),

        array('answers'),
    );

    public static $evaluation_tags = array(
        'avaliacao',
        'nome',
        'questionario',
        'inicio',
        'termino',
    );

    public static $validates_presence_of = array(
        array('name'),
        array('start_at'),
        array('end_at'),
        array('user_id'),
    );

    public static $validates_uniqueness_of = array(
        array('name', 'message' => 'Avaliação já existe'),
    );

    public static $status = array(
        0 => 'Não Enviada',
        1 => 'Enviada',
        2 => 'Respondendo',
        3 => 'Respondida',
        4 => 'Corrigindo',
        5 => 'Corrigida',
    );

    public static $validates_size_of = array(
        array('name', 'within' => array(5, 120)),
    );

    public function can_delete()
    {
        if ($this->evaluation_sendings) {
            foreach ($this->evaluation_sendings as $evaluation_sending) {
                if ($evaluation_sending->answers) {
                    return false;
                }
            }
        }

        return true;
    }

    public function getEnd()
    {
        return date('D', strtotime($this->end_at)).' de '.mes(date('m', strtotime($this->end_at))).' de '.date('Y', strtotime($this->end_at));
    }

    public function before_create()
    {
    }

    public function getSubject()
    {
        $subject = trim($this->subject);

        return $subject == '' ? $this->name : $subject;
    }

    public function validate()
    {
        $date = date('Y-m-d H:i:s');

        if ($this->start_at and $this->start_at < $date) {
            $this->errors->add('Error 1', 'Data Inicial ja passou');
        }

        if ($this->end_at and $this->end_at < $date) {
            $this->errors->add('Error 2', 'Data Término ja passou');
        }

        if ($this->start_at and !$this->end_at) {
            $this->errors->add('Error 3', 'Informe a data de Termino');
        }

        if ($this->start_at and $this->end_at) {
            if ($this->start_at > $this->end_at) {
                $this->errors->add('Error 3', 'Data inicial não pode ser maior que a data de termino');
            }

            // if ($this->start_at == $this->end_at) {
            // 	$this->errors->add('Error 4', 'Data inicial não pode ser igual a data de termino');
            // }
        }
    }

    public function getStatus()
    {
        if ($this->start_at == '') {
            return 'Início não definido';
        }

        if ($this->end_at == '') {
            return 'Término não definido';
        }

        $start_at = strtotime($this->start_at);
        $end_at = strtotime($this->end_at);
        $now = strtotime('now');

        if ($start_at > $now) {
            return 'A Iniciar';
        } elseif ($start_at < $now and $now < $end_at) {
            return 'Aberta';
        } elseif ($now > $end_at) {
            return 'Expirada';
        } else {
            return '-';
        }
    }

    public static function send_mail_admins($subject, $message)
    {
        $admins = User::find_all_by_profile_type('admin');

        $input = array(
            'subject' => $subject,
            'message' => $message,
        );

        $fails = array();

        foreach ($admins as $admin) {
            $input['to']['email'] = $admin->email;
            $input['to']['name'] = $admin->name;

            $mail = new Mail($input);

            $send = $mail->send();

            if (isset($send['error'])) {
                array_push($fails, $admin->email);
            }
        }

        return $fails;
    }

    public function evaluation_sendings()
    {
        return $this->evaluation_sendings;
    }

    public function getMailBcc()
    {
        if ($this->mail_bcc == '') {
            return $this->mail_bcc;
        }

        $mail_bcc = json_decode($this->mail_bcc);

        $mails = array();

        foreach ($mail_bcc as $mail => $name) {
            $mails[] = $mail.Mail::SEPARATOR_FIND.$name;
        }

        return implode(Mail::SEPARATOR_LIST.' ', $mails);
    }

    public function sendings()
    {
        return $this->sendings;
    }

    public function evaluation_questionnaires()
    {
        return $this->evaluation_questionnaires;
    }

    public function questionnaires()
    {
        return $this->questionnaires;
    }

    public function evaluation_groups()
    {
        return $this->evaluation_groups;
    }

    public function evaluation_evaluators()
    {
        return $this->evaluation_evaluators;
    }

    public function getFullValueds()
    {
        $valueds = array();

        foreach ($this->evaluation_groups as $evaluation_groups) {
            $group = $evaluation_groups->group;

            foreach ($group->members as $member) {
                array_push($valueds, $member);
            }
        }

        return $valueds;
    }

    public function sent($evaluation_sending)
    {
        $valueds = $this->getFullValueds();

        if (empty($valueds)) {
            return false;
        }

        $sendings = array();

        foreach ($valueds as $valued) {
            $sending = Sending::create(array(
                'valued_id' => $valued->id,
                'evaluation_sending_id' => $evaluation_sending->id,
            ));

            if ($sending->is_invalid()) {
                return $sending->errors->full_messages();
            }

            array_push($sendings, $sending);
        }

        $this->update_attributes(array('status' => 1));

        return $sendings;
    }

    public function copy(array $attributes)
    {
        $evaluation = self::create($attributes);

        if ($evaluation->is_invalid()) {
            return $evaluation->errors->full_messages();
        }

        foreach ($this->evaluation_groups as $group) {
            EvaluationGroup::create(array(
                'evaluation_id' => $evaluation->id,
                'group_id' => $group->group_id,
            ));
        }

        foreach ($this->evaluation_evaluators as $evaluator) {
            EvaluationEvaluator::create(array(
                'evaluation_id' => $evaluation->id,
                'evaluator_id' => $evaluator->evaluator_id,
            ));
        }

        foreach ($this->evaluation_questionnaires as $questionnaire) {
            EvaluationQuestionnaire::create(array(
                'evaluation_id' => $evaluation->id,
                'questionnaire_id' => $questionnaire->questionnaire_id,
            ));
        }

        return $evaluation;
    }

    public function before_destroy()
    {
        $options = array(
            'conditions' => array(
                'evaluation_id' => $this->id, ),
        );

        EvaluationEvaluator::delete_all($options);
        EvaluationGroup::delete_all($options);
        EvaluationQuestionnaire::delete_all($options);

        $evaluation_sendings = EvaluationSending::all($options);

        foreach ($evaluation_sendings as $evaluation_sending) {
            $evaluation_sending->delete();
        }
    }
}
