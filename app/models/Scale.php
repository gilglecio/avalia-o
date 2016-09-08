<?php

class Scale extends Model
{
    public static $has_many = array(
        array('options', 'class_name' => 'ScaleOption'),
    );

    public static $validates_presence_of = array(
        array('name'),
    );

    public static $validates_uniqueness_of = array(
        array('name'),
    );

    public static $validates_size_of = array(
        array('name', 'within' => array(3, 60)),
    );

    public function options()
    {
        return $this->options;
    }

    public function before_destroy()
    {
        $options = array(
            'conditions' => array(
                'scale_id' => $this->id, ),
        );

        IssueScale::delete_all($options);
    }

    public static function uniqueness(array $attributes)
    {
        $find = self::find(array(
            'conditions' => array('name=?', $attributes['name']),
        ));

        if ($find) {
            return $find;
        }

        $create = self::create($attributes);

        return $create;
    }
}
