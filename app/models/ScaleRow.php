<?php

class ScaleRow extends Model
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $position;

    public static $has_many = array(
        array('scale_options'),
        array('scales', 'through' => 'scale_options'),
    );

    public static $validates_presence_of = array(
        array('name'),
    );

    public static $validates_size_of = array(
        array('name', 'within' => array(5, 60)),
    );

    public function before_destroy()
    {
        $options = array(
            'conditions' => array(
                'questionnaire_id' => $this->id, ),
        );

        ScaleOptions::delete_all($options);
    }

    public static function uniqueness(array $attributes)
    {
        $find = self::find(array(
            'conditions' => array('name=?', $attributes['name']),
        ));

        if ($find) {
            $find->update_attributes(array(
                'position' => $attributes['position'],
            ));

            return $find;
        }

        $create = self::create(array(
            'name' => $attributes['name'],
            'position' => $attributes['position'],
        ));

        return $create;
    }
}
