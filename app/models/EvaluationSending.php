<?php

class EvaluationSending extends Model
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $evaluation_id;

    /**
     * @var \Datetime
     */
    protected $created_at;

    /**
     * @var string
     */
    protected $name;

    public static $belongs_to = array(
        array('evaluation'),
    );

    public static $has_many = array(
        array('sendings'),
        array('answers'),
        array('averages'),
    );

    public static $validates_presence_of = array(
        array('evaluation_id'),
    );

    public function validade()
    {
        $this->errors->add('nome', 'nome é igual');

        return false;
        // $evaluation = Evaluation::find_by_id($this->evaluation_id);
        // $errors = array();

        // // verificar usuarios
        // if (count($evaluation->evaluation_groups)) {

        // 	foreach ($$evaluation->groups as $group) {
        // 		if ( ! count($group->group_members)) {
        // 			array_push($errors, 'Grupo '.$group->name.': VAZIO');
        // 		}
        // 	}

        // } else {
        // 	array_push($errors, 'Nenhum grupo foi adicionado');
        // }

        // // verificar questoes
        // if (count($evaluation->evaluation_questionnaires)) {

        // 	foreach ($$evaluation->questionnaires as $questionnaire) {
        // 		if ( ! count($questionnaire->questionnaire_issues)) {
        // 			array_push($errors, 'Questionário '.$questionnaire->name.': VAZIO');
        // 		}
        // 	}

        // } else {
        // 	array_push($errors, 'Nenhum Questionário foi adicionado');
        // }

        // $this->errors->add('errors', $errors);

        // return $errors ? $errors : array('Sucesso');
    }

    public function getStatus()
    {
        $status['viewed'] = Sending::all(array(
            'conditions' => array(
                'evaluation_sending_id = ? AND viewed_at is not null', $this->id, ), ));

        $status['answered'] = Sending::all(array(
            'conditions' => array(
                'evaluation_sending_id = ? AND answered_at is not null', $this->id, ), ));

        $status['corrected'] = Sending::all(array(
            'conditions' => array(
                'evaluation_sending_id = ? AND corrected_at is not null', $this->id, ), ));

        return $status;
    }

    public function countStatus()
    {
        $count = array();

        foreach (self::getStatus() as $key => $rows) {
            $count[$key] = count($rows);
        }

        return $count;
    }

    public function evaluation()
    {
        return $this->evaluation;
    }

    public function sendings()
    {
        return $this->sendings;
    }

    public function before_destroy()
    {
        $options = array(
            'conditions' => array(
                'evaluation_sending_id' => $this->id, ),
        );

        $sendings = Sending::all($options);

        foreach ($sendings as $sending) {
            $sending->delete();
        }

        foreach (Answer::all($options) as $answer) {
            $answer->delete();
        }

        $sending_evaluators = SendingEvaluator::all($options);

        foreach ($sending_evaluators as $sending_evaluator) {
            $sending_evaluator->delete();
        }

        SendingBcc::delete_all($options);
    }
}
