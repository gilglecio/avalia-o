<?php

class SendingEvaluator extends Model
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $evaluator_id;

    /**
     * @var int
     */
    protected $evaluation_sending_id;

    /**
     * @var int
     */
    protected $sending_id;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var \Datetime
     */
    protected $created_at;

    /**
     * @var int
     */
    protected $evaluate;

    /**
     * @var bool
     */
    protected $is_corrected;

    public static $belongs_to = array(
        array('evaluation_sending'),
        array('evaluator', 'class_name' => 'User', 'foreign_key' => 'evaluator_id'),
    );

    public static $validates_uniqueness_of = array(
        array('token'),
    );

    public static $validates_presence_of = array(
        array('evaluator_id'),
        array('evaluation_sending_id'),
        array('sending_id'),
    );

    public function evaluator()
    {
        return $this->evaluator;
    }

    public function getEncodeToken()
    {
        return Sending::encodeToken($this->token);
    }

    public function before_destroy()
    {
        $options = array('conditions' => array('evaluation_sending_id' => $this->id));

        Sending::delete_all($options);
    }

    public function before_create()
    {
        $this->token = crypt($this->evaluation_sending_id.$this->evaluator_id);
    }

    public static function uniqueness($attributes)
    {
        $find = self::find(array(
            'conditions' => array(
                'evaluator_id=? AND evaluation_sending_id=?',
                $attributes['evaluator_id'],
                $attributes['evaluation_sending_id'],
            ),
        ));

        if ($find) {
            if ($find->sending_id == 0) {
                $find->update_attributes(array(
                    'sending_id' => $attributes['sending_id'],
                ));
            }

            return $find;
        }

        $create = self::create($attributes);

        return $create;
    }
}
