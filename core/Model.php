<?php
/**
 * Created by PhpStorm.
 * User: langstra
 * Date: 14-12-14
 * Time: 21:02
 */

class Model {

    public $db;
    public $token;

    function __construct($db)
    {
        $this->db = $db;

        require_once MODELS_PATH . 'token_model.php';
        $this->token = new Token($db);
    }


    /**
     * Checks if there are required keys which is are not used in the $values array
     *
     * @param array $values Key values pairs to check
     * @param array $required Required values to which the keys must match
     * @return array|bool - Returns the keys which are not present, true if all are present
     */
    public function empty_values($values, $required) {
        $value_keys = array_keys($values);
        $empty_values = null;
        foreach($required as $r) {
            if(!in_array($r, $value_keys)) {
                $empty_values[] = $r;
            }
        }
        return $empty_values === null ? true : $empty_values;
    }

    public function filter_parameters($array, $allowed) {
        $return_array = array();

        foreach($array as $k => $v) {
            if(in_array($k, $allowed)) {
                $return_array[$k] = $v;
            }
        }

        return $return_array;
    }

    public function auth_error() {
        return array('error' => 36000);
    }

    public function permission_error() {
        return array('error' => 36001);
    }

    public function what_error() {
        return array('error' => 36002);
    }

    public function login_error() {
        return array('error' => 36003);
    }

    public function indentifier_error() {
        return array('error' => 36004);
    }

    public function param_error() {
        return array('error' => 36005);
    }

    public function no_company_error() {
        return array('error' => 36006);
    }

    public function create_error($fields) {
        return array('create_error' => $fields);
    }

    public function update_error($fields) {
        return array('update_error' => $fields);
    }

    public function return_true() {
        return array('true');
    }
}