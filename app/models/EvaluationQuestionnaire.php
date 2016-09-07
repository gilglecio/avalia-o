<?php

class EvaluationQuestionnaire extends Model
{
    /**
     * @var int
     */
    protected $evaluation_id;

    /**
     * @var int
     */
    protected $questionnaire_id;

    public static $belongs_to = array(
        array('evaluation'),
        array('questionnaire'),
    );

    public static $validates_presence_of = array(
        array('evaluation_id'),
        array('questionnaire_id'),
    );

    public function questionnaire()
    {
        return $this->questionnaire;
    }
}
