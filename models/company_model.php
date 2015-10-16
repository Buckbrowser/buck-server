<?php
/**
 * Created by PhpStorm.
 * User: Langstra
 * Date: 30-12-2014
 * Time: 16:26
 */

class Company extends Model{


    /**
     * Calls parent with database connection
     * @param $db
     */
    function __construct($db)
    {
        parent::__construct($db);
    }

    /**
     * @param $params
     * @return array
     */
    public function create($params) {
        $v = new \Valitron\Validator($params);
        $v->rule('required', ['token', 'name', 'email']);
        $used_values = null;
        $return_errors = null;

        if($v->validate()) {
            if(($token = $this->token->validate($params['token'])) !== false) {
                $params = $this->filter_parameters($params, array('name', 'street_name', 'house_number', 'zipcode', 'place_name', 'id_country', 'email', 'tax_number', 'company_registration_number', 'default_invoice_number_prefix', 'default_payment_term'));
                $v->rule('email', 'email');
                if ($this->email_used($params['email'])) $used_values[] = 'email';
                if ($used_values === null) {
                    if ($v->validate()) {
                        $sql = "INSERT INTO company (";
                        foreach ($params as $key => $value) {
                            $sql .= $key . ",";
                        }
                        $sql = substr($sql, 0, -1);
                        $sql .= ") VALUES (";
                        foreach ($params as $key => $value) {
                            $sql .= " :" . $key . ",";
                            $params[':' . $key] = $value;
                        }
                        $sql = substr($sql, 0, -1);
                        $sql .= ")";
                        $query = $this->db->prepare($sql);
                        $query->execute($params);
                        $company_id = $this->get_company_id($params['name']);

                        $sql = "INSERT INTO company_has_user (id_user, id_company) VALUES (?,?)";
                        $query = $this->db->prepare($sql);
                        $query->execute([$token['id_user'], $company_id]);

                        //@todo Send an email to the user with confirmation of his registration
                        return array('id' => $company_id);
                    } else {
                        $return_errors['incorrect_fields'] = 'email';
                    }
                } else {
                    $return_errors['already_exists'] = $used_values;
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
     * Check if an email address is already used
     *
     * @param string $email Email address to check
     * @return bool True if the email address is already used
     */
    private function email_used($email)
    {
        $sql = "SELECT count(*) AS company FROM company WHERE email = :email";
        $query = $this->db->prepare($sql);
        $parameters = array(':email' => $email);
        $query->execute($parameters);
        $result = $query->fetch();
        return $result->company != 0;
    }

    /**
     * Get the company id by name
     *
     * @param string $company Company name to check
     * @return mixed Company id if the user exists, otherwise false
     */
    public function get_company_id($company)
    {
        $sql = "SELECT id FROM company WHERE name = :name";
        $query = $this->db->prepare($sql);
        $parameters = array(':name' => $company);
        $query->execute($parameters);
        if ($query->rowCount() > 0) {
            return $query->fetch()->id;
        } else {
            return false;
        }
    }

    private function get_company($id)
    {
        $sql = 'SELECT name, street_name, house_number, zipcode, place_name, id_country, email, tax_number, company_registration_number, default_payment_term, default_invoice_number_prefix, registration_date FROM company WHERE id = :id_company AND deleted_at IS NULL';
        $query = $this->db->prepare($sql);
        $parameters = array(':id_company' => $id);
        $query->execute($parameters);
        $result = $query->fetch();
        if($query->rowCount() > 0) {
            return array(
                'name' => $result->name,
                'street_name' => $result->street_name,
                'house_number' => $result->house_number,
                'zipcode' => $result->zipcode,
                'place_name' => $result->place_name,
                'id_country' => $result->id_country,
                'email' => $result->email,
                'tax_number' => $result->tax_number,
                'company_registration_number' => $result->company_registration_number,
                'default_payment_term' => $result->default_payment_term,
                'default_invoice_number_prefix' => $result->default_invoice_number_prefix,
                'registration_date' => $result->registration_date
            );
        } else {
            return false;
        }
    }

    /**
     * Get all the information from a company
     * @param Array $params Token is required
     */
    public function read($params)
    {
        $v = new Valitron\Validator($params);
        $v->rule('required', 'token');

        if ($v->validate()) {
            if (($token = $this->token->validate($params['token'])) !== false) {
                if($company = $this->get_company($token['id_company'])) {
                    return $company;
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
     * Update the details of the company the user is currently logged onto.
     * Authorization: 1
     * @param array $params Token is required. Other keys can be: name, street_name, house_number, zipcode, place_name, id_country, email, tax_number, company_registration_number, default_invoice_number_prefix, default_payment_term
     */
    public function update($params)
    {
        $v = new Valitron\Validator($params);
        $v->rule('required', 'token');

        if ($v->validate()) {
            if (($token = $this->token->validate($params['token'], 1)) !== false) {
                $params = $this->filter_parameters($params, array('name', 'street_name', 'house_number', 'zipcode', 'place_name', 'id_country', 'email', 'tax_number', 'company_registration_number', 'default_invoice_number_prefix', 'default_payment_term'));

                if ($v->validate()) {
                    $sql = 'UPDATE company SET';
                    foreach ($params as $key => $value) {
                        $sql .= ' ' . $key . ' = :' . $key . ',';
                        $parameters[':' . $key] = $value;
                    }
                    $sql = substr($sql, 0, -1);
                    $sql .= ' WHERE id = :company_id';
                    $parameters[':company_id'] = $token['id_company'];

                    $query = $this->db->prepare($sql);

                    if($query->execute($parameters)) {
                        return $this->return_true();
                    } else {
                        return $this->what_error();
                    }
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

    public function delete($params)
    {
        $v = new Valitron\Validator($params);
        $v->rule('required', 'token');

        if ($v->validate()) {
            if (($token = $this->token->validate($params['token'], 1)) !== false) {
                if(isset($params['verification_code'])) {
                    $verify_code_sql = "SELECT * FROM verify_delete_company WHERE code = :code AND (created_at + INTERVAL 1 DAY) > NOW()";
                    $verify_code_query = $this->db->prepare($verify_code_sql);
                    $parameters = [':code' => $params['verification_code']];
                    $verify_code_query->execute($parameters);
                    if($verify_code_query->execute($parameters) && $verify_code_query->rowCount() > 0) {
                        $result = $verify_code_query->fetch();
                        if ($result->company_id == $token['id_company']) {
                            $delete_company_sql = "UPDATE company SET deleted_at = NOW() WHERE id = :company_id";
                            $parameters = [':company_id' => $result->company_id];
                            $delete_company_query = $this->db->prepare($delete_company_sql);
                            if ($delete_company_query->execute($parameters)) {
                                return $this->return_true();
                            } else {
                                $this->what_error();
                            }
                        }
                    } else {
                        return $this->what_error();
                    }

                } else if (isset($params['url']) && strpos($params['url'], '%verification-code%')) {
                    //create delete verification code and mail it

                    $sql = "INSERT INTO verify_delete_company (company_id, code) VALUES (:company_id, :code)";
                    $query = $this->db->prepare($sql);
                    $verification_code = bin2hex(openssl_random_pseudo_bytes(32));
                    $parameters = [':company_id' => $token['id_company'], ':code' => $verification_code];
                    if($query->execute($parameters)) {


                        $company = $this->get_company($token['id_company']);

                        require_once 'core/Mail.php';
                        $mail = new Mail();

                        $mail->addAddress($company['email'], $company['name']);

                        $mail->isHTML(true);

                        $mail->Subject = "Deleting your company";
                        $mail->Body = str_replace(
                            ['%company%', '%bb-link%'],
                            [$company['name'], str_replace('%verification-code%', $verification_code, addslashes($params['url']))],
                            file_get_contents(TEMPLATE_PATH . 'mail/verify_company_delete.html')
                        );
                        if($mail->send()) {
                            return $this->return_true();
                        }
                    } else {
                        return $this->what_error();
                    }
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
}