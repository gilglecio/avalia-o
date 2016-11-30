<?php

class User extends Model
{
    public static $has_many = array(
        array('user_ratings'),
        array('ratings', 'through' => 'user_ratings'),
        array('answers', 'foreign_key' => 'valued_id'),

        array('user_charges'),
        array('charges', 'through' => 'user_charges'),
        array('evaluation_evaluators', 'foreign_key' => 'evaluator_id'),

        array('group_members'),
        array('user_groups', 'class_name' => 'GroupMember'),
        array('groups', 'through' => 'group_members'),
        array('corrections', 'foreign_key' => 'evaluator_id'),
        array('pdfs', 'foreign_key' => 'evaluator_id'),
    );

    public static $belongs_to = array();

    public static $profile_types = array(
        'appraiser' => 'Avaliador',
        'valued' => 'Avaliado',
        'admin' => 'Administrador',
    );

    public static $validates_presence_of = array(
        array('name'),
        array('email'),
        array('birth'),
        array('profile_type'),
    );

    public static $validates_uniqueness_of = array(
        array(['email', 'profile_type'], 'message' => 'já existe.')
    );

    public static $validates_format_of = array(
        array('email', 'with' => '/^[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/'),
    );

    public static $validates_inclusion_of = array(
        array('profile_type', 'in' => array('appraiser', 'valued', 'admin')),
    );

    public function evaluation_evaluators()
    {
        return $this->evaluation_evaluators;
    }

    public function getSalary()
    {
        return number_format($this->salary, 2, ',', '.');
    }

    public function getProfileType()
    {
        return self::$profile_types[$this->profile_type];
    }

    public function can_delete()
    {
        // if ($this->evaluation_evaluators)
        // 	return false;

        // if ($this->group_members) {
        // 	$shift = array_shift($this->group_members);
        // 	$evaluation_groups = EvaluationGroup::find_all_by_group_id($shift->group_id);
        // 	if ($evaluation_groups) {
        // 		return false;
        // 	}
        // }

        if ($this->id == self::getUserLogger('id')) {
            return false;
        }

        if ($this->answers) {
            return 'Este esta participando de uma avaliação';
        }

        if ($this->corrections) {
            return 'Este esta participando de uma avaliação';
        }

        return true;
    }

    public function getNameCharges()
    {
        $names = array();

        foreach ($this->charges as $charge) {
            array_push($names, $charge->name);
        }

        return $names;
    }

    public function getNameGroups()
    {
        $names = array();

        foreach ($this->groups as $group) {
            array_push($names, $group->name);
        }

        return $names;
    }

    public function getNameRatings()
    {
        $names = array();

        foreach ($this->ratings as $rating) {
            array_push($names, $rating->name);
        }

        return $names;
    }

    public function evaluations()
    {
        return array();
    }

    public function firstName()
    {
        $split = explode(' ', $this->name);

        return $split[0];
    }

    public function before_save()
    {
        $find = self::all(array(
            'conditions' => array(
                'email = ?',
                $this->email,
            ),
        ));

        if (count($find) == 2) {
            return 'Você excedeu o limite de (02) cadastros por e-mail (Avaliado e Avaliador).';
        }

        if (count($find) == 1) {
            $find = $find[0];
            if ($find->profile_type == $this->profile_type) {
                return 'E-mail "'.$this->email.'" ja esta cadastrado como "'.self::$profile_types[$this->profile_type].'", tente outro perfil.';
            }
        }

        if ($find) {
            if ($find->profile_type == $this->profile_type) {
                return false;
            }
        }
    }

    public static function getAnswersByEvaluation($evaluation)
    {
        $group = array();

        $answers = Answer::all(array(
            'conditions' => array(
                'evaluation_id' => $evaluation->id,
            ),
        ));

        foreach ($answers as $answer) {
            if (!isset($group[$answer->valued_id]['valued'])) {
                $group[$answer->valued_id]['valued'] = $answer->valued;
            }

            $group[$answer->valued_id]['answers'][$answer->issue_id] = $answer;
        }

        return $group;
    }

    public static function mailIsValid($email, $separator = ',')
    {
        $pattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';

        $split = explode($separator, $email);

        $array = array(
            'valid' => array(),
            'invalid' => array(),
        );

        foreach ($split as $key => $email) {
            $email = trim(strip_tags($email));

            if (!preg_match($pattern, $email)) {
                $array['invalid'][] = $email;
            } else {
                $array['valid'][] = $email;
            }
        }

        return $array;
    }

    public static function getUserLogger($column = null)
    {
        $user = self::find_by_id($_SESSION['app.user_id']);

        if (!$user) {
            return false;
        }

        if ($column and $user->$column) {
            return $user->$column;
        }

        return $user;
    }

    public static function crypt_password($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function credentials()
    {
        $options = array(
            'select' => 'username, password',
            'conditions' => array(
                'profile_type=?',
                'admin',
            ),
        );

        $users = self::all($options);

        if ($users) {
            $credentials = array();
            foreach ($users as $user) {
                $credentials[$user->username] = $user->password;
            }

            return $credentials;
        }

        return array();
    }

    public function getRatings()
    {
        return $this->ratings;
    }

    public function getCharges()
    {
        return $this->charges;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function before_destroy()
    {
        $evaluation_evaluators = EvaluationEvaluator::find_by_evaluator_id($this->id);

        if ($evaluation_evaluators) {
            return false;
        }

        $options = array(
            'conditions' => array(
                'user_id' => $this->id, ),
        );

        UserCharge::delete_all($options);
        UserRating::delete_all($options);
        GroupMember::delete_all($options);
        Sending::delete_all(array(
            'conditions' => array(
                'valued_id' => $this->id,
            ),
        ));

        Correction::delete_all(array(
            'conditions' => array(
                'evaluator_id' => $this->id,
            ),
        ));

        EvaluationEvaluator::delete_all(array(
            'conditions' => array(
                'evaluator_id' => $this->id,
            ),
        ));

        Answer::delete_all(array(
            'conditions' => array(
                'valued_id' => $this->id,
            ),
        ));
    }

    public function profile()
    {
        if (isset(self::$profile_types[$this->profile_type])) {
            return self::$profile_types[$this->profile_type];
        } else {
            return '-';
        }
    }
}
