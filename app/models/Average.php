<?php

class Average extends Model
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
    protected $valued_id;

    /**
     * @var int
     */
    protected $evaluation_sending_id;

    /**
     * @var decimal
     */
    protected $average;

    /**
     * @var \Datetime
     */
    protected $created_at;

    /**
     * @var \Datetime
     */
    protected $updated_at;

    /**
     * @var int
     */
    protected $status;

    public static $belongs_to = array(
        array('evaluation'),
        array('valued', 'class_name' => 'User', 'primary_key' => 'id', 'foreign_key' => 'valued_id'),
        array('evaluation_sending'),
    );

    public static $validates_presence_of = array(
        array('average'),
        array('evaluation_id'),
        array('valued_id'),
    );

    public function average()
    {
        return number_format($this->average, 2, ',');
    }

    public function valued()
    {
        return $this->valued;
    }

    public function evaluation()
    {
        return $this->evaluation;
    }
}
