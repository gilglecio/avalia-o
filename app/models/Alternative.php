<?php

class Alternative extends Model
{
    public static $has_many = array(
        array('issue_alternatives'),
        array('alternatives', 'through' => 'issue_alternatives'),
    );

    public static $validates_presence_of = array(
        array('name'),
    );

    public static $validates_uniqueness_of = array(
        array('name'),
    );

    public static $validates_size_of = array(
        array('name', 'within' => array(1, 60)),
    );

    public function before_destroy()
    {
        $options = array(
            'conditions' => array(
                'alternative_id' => $this->id, ),
        );
    }

    public static function uniqueness(array $attributes)
    {
        $find = self::find(array(
            'conditions' => array('name=?', $attributes['name']),
        ));

        if ($find) {
            return $find;
        }

        $create = self::create(array(
            'name' => $attributes['name'],
        ));

        return $create;
    }

    public static function getNames()
    {
        $all = self::all();

        $names = array();

        foreach ($all as $alternative) {
            $names[$alternative->id] = $alternative->name;
        }

        return $names;
    }
}
