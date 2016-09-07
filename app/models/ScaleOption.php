<?php

class ScaleOption extends Model
{
    /**
     * @var int
     */
    protected $scale_id;

    /**
     * @var int
     */
    protected $scale_row_id;

    /**
     * @var int
     */
    protected $position;

    public static $belongs_to = array(
        array('scale'),
        array('row', 'class_name' => 'ScaleRow'),
    );

    public static $validates_presence_of = array(
        array('scale_id'),
        array('scale_row_id'),
    );

    public function row()
    {
        return $this->row;
    }

    public static function uniqueness(array $attributes)
    {
        $find = self::find(array(
            'conditions' => array('scale_id=? AND scale_row_id=?', $attributes['scale_id'], $attributes['scale_row_id']),
        ));

        if ($find) {
            return $find;
        }

        $create = self::create($attributes);

        return $create;
    }
}
