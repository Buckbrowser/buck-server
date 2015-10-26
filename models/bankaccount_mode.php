<?php

/**
 * Created by PhpStorm.
 * User: langstra
 * Date: 17-12-14
 * Time: 11:01
 */
class BankAccount extends Model
{


    /**
     * Includes passwordhash for creating and verifying password
     */
    function __construct($db)
    {
        parent::__construct($db);
    }


    /**
     * @param array $params Parameters for creating a contact
     *                      token, company, first_name, last_name, email, street_name, house_number, zipcode, place_name are required
     *                      id_country, default_payment_term, default_auto_reminder are optional
     * @return array
     */
    public function create($params)
    {
        $params = $this->filter_parameters($params, array('token', 'account_holder', 'iban', 'bic'));
        $v = new \Valitron\Validator($params);
        $v->rules('required', ['token', 'account_holder', 'iban', 'bic']);

        if ($v->validate()) {
            if (($token = $this->token->validate($params['token'])) !== false) {
                $v->rule('iban', 'iban');
                if ($v->validate()) {

                    unset($params['token']);
                    $sql = "INSERT INTO bank_account (";
                    foreach ($params as $key => $value) {
                        $sql .= $key . ",";
                    }
                    $sql .= "id_company";
                    $sql .= ") VALUES (";
                    foreach ($params as $key => $value) {
                        $sql .= " :" . $key . ",";
                        $params[':' . $key] = $value;
                    }
                    $sql .= ":id_company";
                    $sql .= ")";
                    $params[":id_company"] = $token['id_company'];
                    $query = $this->db->prepare($sql);

                    $this->db->beginTransaction();
                    echo $sql;
                    if (!$query->execute($params)) {
                        $this->db->rollBack();
                        return $this->what_error();
                    } else {
                        $this->db->commit();
                        return $this->return_true();
                    }
                } else {
                    $return_errors['incorrect_fields'] = 'iban';
                }
            } else {
                return $this->auth_error();
            }
        } else {
            $return_errors['empty_fields'] = array_keys($v->errors());
        }
        return $this->create_error($return_errors);
    }

    /**
     * Get all the user information
     * @param array $params Token is required
     */
    public function read($params)
    {
        $v = new Valitron\Validator($params);
        $v->rule('required', ['token', 'id']);

        if ($v->validate()) {
            if (($token = $this->token->validate($params['token'])) !== false) {
                $sql = 'SELECT * FROM bank_account WHERE id_company = :company_id AND id = :bank_account_id';
                $query = $this->db->prepare($sql);
                $parameters = [':bank_account_id' => $params['id'], ':company_id' => $token['id_company']];
                $query->execute($parameters);
                if ($query->rowCount() > 0) {
                    $result = $query->fetch();
                    return array(
                        'account_holder' => $result->account_holder,
                        'iban' => $result->iban,
                        'bic' => $result->bic
                    );
                } else {
                    return $this->what_error();
                }
            } else {
                return $this->auth_error();
            }
        } else {
            return $this->param_error();
        }
    }

    /**
     * Update the given user details
     * @param array $params Token is required. Other keys can be: first_name, last_name, password, email, language
     */
    public function update($params)
    {
        $v = new Valitron\Validator($params);
        $v->rule('required', 'token');

        if ($v->validate()) {
            if (($user = $this->token->validate($params['token'])) !== false) {
                $params = $this->filter_parameters($params, array('password', 'email', 'language', 'first_name', 'last_name'));
                $v->rule('email', 'email');
                if ($v->validate()) {

                    if (isset($params['password'])) {
                        $params['password'] = create_hash($params['password']);
                    }
                    $sql = 'UPDATE user SET';
                    foreach ($params as $key => $value) {
                        $sql .= ' ' . $key . ' = :' . $key . ',';
                        $parameters[':' . $key] = $value;
                    }
                    $sql = substr($sql, 0, -1);
                    $sql .= ' WHERE id = :userid';
                    $parameters[':userid'] = $user['id_user'];

                    $query = $this->db->prepare($sql);
                    $query->execute($parameters);

                    return $this->return_true();
                } else {
                    return $this->update_error(array_keys($v->errors()));
                }
            } else {
                return $this->auth_error();
            }
        } else {
            return $this->param_error();
        }
    }

} 