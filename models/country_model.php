<?php
/**
 * Created by PhpStorm.
 * User: langstra
 * Date: 18-12-14
 * Time: 11:01
 */

class Country extends Model{


    /**
     *
     */
    function __construct($db)
    {
        parent::__construct($db);
    }


    /**
     * Gets the country information by id
     *
     * @param array $params Array with params, id is required
     *
     * @return array Name and locale of the country
     */
    public function read($params) {

        $v = new Valitron\Validator($params);
        $v->rule('required', 'id');

        if ($v->validate()) {

            if ($this->empty_values($params, array('id')) === true) {
                $sql = "SELECT name, locale FROM country WHERE id = :id";
                $query = $this->db->prepare($sql);
                $parameters = array(':id' => $params['id']);
                $query->execute($parameters);
                $result = $query->fetch();
                return array('name' => $result->name, 'locale' => $result->locale);
            } else {
                return $this->indentifier_error();
            }
        } else {
            return $this->param_error();
        }
    }

    /**
     * Gets all the countries in the database
     *
     * @param array $params No arguments are required
     *
     * @return array Contains arrays with id, name, and locale
     */
    public function get_all($params) {
        $sql = "SELECT id, name, locale FROM country";
        $query = $this->db->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}