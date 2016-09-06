<?php

class Sending extends Model
{
    public static $belongs_to = array(
        array('evaluation_sending'),
        array('valued', 'class_name' => 'User', 'primary_key' => 'id', 'foreign_key' => 'valued_id')
    );

    public static $after_create = array(
        'send_email_valued'
    );

    public static $validates_presence_of = array(
        array('evaluation_sending_id'),
        array('valued_id'),
        array('status')
    );

    public static $validates_uniqueness_of = array(
        array('token')
    );

    public static $status = array(
        0 => 'Não visualizado',
        1 => 'Visualizado',
        2 => 'Respondendo',
        3 => 'Respondido',
        4 => 'Corrigindo',
        5 => 'Avaliado',
        6 => 'Aprovado',
        7 => 'Reprovado'
    );

    public function getAnswered($type = null)
    {
        switch ($type) {
            case 'full':
                return full_date($this->answered_at);
                break;
            
            default:
                return $this->answered_at;
                break;
        }
    }

    public function isCorrectedByEvaluator($evaluator_id)
    {
        $answers = Answer::find_all_by_valued_id_and_evaluation_sending_id($this->valued_id, $this->evaluation_sending_id);

        foreach ($answers as $answer) {
            $corrections = $answer->corrections;
            foreach ($corrections as $correction) {
                if ($correction->evaluator_id == $evaluator_id) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getMedia($evaluator_id = null)
    {
        $answers =  Answer::find_all_by_valued_id_and_evaluation_sending_id($this->valued_id, $this->evaluation_sending_id);

        $data = array();

        foreach ($answers as $answer) {
            $corrections = $answer->corrections;

            foreach ($corrections as $correction) {
                if (! isset($data['sum'][$correction->evaluator_id])) {
                    $data['sum'][$correction->evaluator_id] = 0;
                }

                $data['sum'][$correction->evaluator_id] += $correction->note;
            }
        }

        if (! isset($data['sum'])) {
            return '-';
        }

        foreach ($data['sum'] as $sum_evaluator_id => $sum) {
            if ($evaluator_id == $sum_evaluator_id) {
                $media = $sum / count($answers);
                return number_format($media, 2, ',', '.');
            }
                
            $media = $sum / count($answers);
            $data['media'][$sum_evaluator_id] = number_format($media, 2, ',', '.');
        }

        if ($evaluator_id) {
            return '-';
        }

        return $data;
    }

    public function evaluation_sending()
    {
        return $this->evaluation_sending;
    }

    public function updated_at()
    {
        return $this->created_at == $this->updated_at ? '' : $this->updated_at;
    }

    public function average()
    {
        return Average::find_by_valued_id($this->valued_id);
    }

    public function getEncodeToken()
    {
        return self::encodeToken($this->token);
    }

    public static function getUrlReply($token)
    {
        return config('domain') . 'reply/' . self::encodeToken($token);
    }

    public static function getUrlCorrection()
    {
        return config('domain') . 'correction/' . self::encodeToken($this->token);
    }

    public static function encodeToken($token)
    {
        return base64_encode($token);
    }

    public static function decodeToken($token)
    {
        return base64_decode($token);
    }

    public function getStatus()
    {
        return self::$status[$this->status];
    }

    public function valued()
    {
        return $this->valued;
    }

    public function send_mail_admin($subject, $message)
    {
        $admins = User::find_all_by_profile_type('admin');

        $input = array(
            'subject' => $subject,
            'message' => $message
        );

        $fails = array();

        foreach ($admins as $admin) {
            $input['to']['email'] = $admin->email;
            $input['to']['name'] = $admin->name;

            $mail = new Mail($input);

            if (ENV_DEFAULT == 'prod') {
                $send = $mail->send();
                if (isset($send['error'])) {
                    array_push($fails, $admin->email);
                }
            }
        }

        return $fails;
    }

    public function send_mail_bcc()
    {
        $evaluation = $this->evaluation_sending->evaluation;

        $input = array(
            'subject' => 'AVALIAÇÃO CORRIGIDA'
        );

        if ($evaluation->mail_bcc == '') {
            return false;
        }

        $mail_bcc = json_decode($evaluation->mail_bcc);

        $fails = array();
        
        foreach ($mail_bcc as $email => $name) {
            $sending_bcc = SendingBcc::uniqueness(array(
                'evaluation_sending_id' => $this->evaluation_sending->id,
                'name' => $name,
                'email' => $email
            ));

            $message = 'Avaliação Corrigida. Visualizar correção, '.config('domain').'not_evaluate/'. self::encodeToken($sending_bcc->token);

            $input['message'] = $message;
            $input['to']['email'] = $email;
            $input['to']['name'] = $name;

            $mail = new Mail($input);
            
            $send = $mail->send();
            if (isset($send['error'])) {
                array_push($fails, $input['to']['email']);
            }
        }

        return $fails;
    }

    public function send_mail_evaluators()
    {
        $evaluation = $this->evaluation_sending->evaluation;
        $evaluation_evaluators = $evaluation->evaluation_evaluators;

        if (empty($evaluation_evaluators)) {
            $subject = 'AVALIAÇÃO SEM AVALIADOR';
            $message = 'A avaliação '.$evaluation->getSubject().' está sem avaliador.';

            return $this->send_mail_admin($subject, $message);
        }

        $input = array(
            'subject' => 'Avaliação Respondida'
        );

        $fails = array();
        
        foreach ($evaluation_evaluators as $evaluation_evaluator) {
            $evaluator = $evaluation_evaluator->evaluator;

            $attributes = array(
                'evaluator_id' => $evaluator->id,
                'evaluation_sending_id' => $this->evaluation_sending->id,
                'sending_id' => $this->id
            );

            $sending_evaluator = SendingEvaluator::uniqueness($attributes);

            // $message = "Prezado avaliador(a),\n\nA autoavaliação de ".join_e(Answer::name_valueds($this->evaluation_sending->id))." já está disponível.\n\nAcesse o link abaixo:\n".config('domain').'correct/'. self::encodeToken($sending_evaluator->token)."\n\nObrigado,\nAvaliação";
            $message = "Prezado avaliador(a),\n\nA autoavaliação de ".$this->valued->name." já está disponível.\n\nAcesse o link abaixo:\n".config('domain').'correct/'. self::encodeToken($sending_evaluator->token).'/valued/'.$this->valued_id.'/sending/'.$this->id."\n\nObrigado,\nAvaliação";

            $input['message'] = $message;
            $input['to']['email'] = $evaluator->email;
            $input['to']['name'] = $evaluator->name;
            
            $mail = new Mail($input);
            
            $send = $mail->send();
            if (isset($send['error'])) {
                array_push($fails, $input['to']['email']);
            }
        }

        return $fails;
    }

    public function after_save()
    {
        if ($this->status == 3) {
            $send_mail_evaluators = $this->send_mail_evaluators();
            $send_mail_bcc = $this->send_mail_bcc();
        }

        if ($this->status == 5) {
            $send_email_valued_corrected = $this->send_email_valued_corrected();
        }

        $fails = array();

        if (isset($send_mail_evaluators['error'])) {
            $fails[] = $send_mail_evaluators['error'];
        }
        if (isset($send_mail_bcc['error'])) {
            $fails[] = $send_mail_bcc['error'];
        }
        if (isset($send_email_valued_corrected['error'])) {
            $fails[] = $send_email_valued_corrected['error'];
        }

        return $fails;
    }

    public function before_destroy()
    {
        $options = array('conditions' => array('sending_id = ?', $this->id));
        SendingEvaluator::delete_all($options);
        Pdf::delete_all($options);
    }

    public function before_create()
    {
        $this->token = crypt($this->evaluation_sending_id.$this->valued_id);

        $find = self::find(array(
            'conditions' => array(
                'valued_id=? AND evaluation_sending_id=? AND status=?',
                $this->valued_id,
                $this->evaluation_sending_id,
                0
            )
        ));

        if ($find) {
            return false;
        }
    }

    public function send_email_valued_corrected()
    {
        return true;
        
        $input = array(
            'subject' => 'Avaliacao Corrigida',
            'to' => array(
                'name' => $this->valued->name,
                'email' => $this->valued->email
            ),
            'message' => 'Questionário respondido. Obrigado(a)!'
        );

        $mail = new Mail($input);
        $fails = array();

        $send = $mail->send();
        if (isset($send['error'])) {
            array_push($fails, $input['to']['email']);
        }

        return $fails;
    }

    public function send_email_valued()
    {
        $evaluation = $this->evaluation_sending->evaluation;

        $replacements = array(
            "#avaliacao" => $evaluation->getSubject(),
            "#nome" => $this->valued->name,
            "#usuario" => $this->valued->name,
            "#inicio" => $evaluation->start_at->format('d/m/Y'),
            "#termino" => $evaluation->end_at->format('d/m/Y'),
            "#questionario" => self::getUrlReply($this->token)
        );

        $input = array(
            'subject' => $evaluation->getSubject(),
            'to' => array(
                'name' => $this->valued->name,
                'email' => $this->valued->email
            ),
            'message' => $evaluation->message_email,
            'replacements' => $replacements
        );

        $mail = new Mail($input);
        $fails = array();

        $send = $mail->send();
        if (isset($send['error'])) {
            array_push($fails, $input['to']['email']);
        }

        return $fails;
    }
}
