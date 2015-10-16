<?php

/**
 * Created by PhpStorm.
 * User: langstra
 * Date: 17-12-14
 * Time: 11:01
 */
class User extends Model
{


    /**
     * Includes passwordhash for creating and verifying password
     */
    function __construct($db)
    {
        parent::__construct($db);
        require_once LIBS_PATH . 'passwordhash.php';
    }


    /**
     * @param array $params Parameters for creating an account
     *                      username, password, email are required
     *                      language, first_name, last_name are optional
     * @return array
     */
    public function create($params)
    {
        $params = $this->filter_parameters($params, array('username', 'password', 'first_name', 'last_name', 'email', 'language'));
        $v = new \Valitron\Validator($params);
        $v->rules([
            'required' => [['username'], ['password'], ['email'], ['language']]
            ]
        );

        $used_values = null;
        $return_errors = null;
        if ($v->validate()) {
            if ($this->get_user_id($params['username']) !== false) $used_values[] = 'username';
            if ($this->email_used($params['email'])) $used_values[] = 'email';
            if ($used_values === null) {
                $v->rule('email', 'email');
                if($v->validate()) {
                    $v->rules(['lengthMax' => [['username', 20]]]);
                    if($v->validate()) {
                        $params['password'] = create_hash($params['password']);
                        $sql = "INSERT INTO user (";
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
                        $user_id = $this->get_user_id($params['username']);

                        $auth = new Token($this->db);

                        require_once 'core/mail.php';

                        $mail = new Mail();

                        $mail->addAddress($params['email'], $params['username']);

                        $mail->isHTML(true);

                        $mail->Subject = "Welcome to buckbrowser";
                        $mail->Body = str_replace(
                            ['%username%', '%bb-link%'],
                            [$params['username'], 'http://buckbrowser.langstra.nl'],
                            file_get_contents(TEMPLATE_PATH . 'mail/signup.html')
                        );
                        $mail->send();
                        return array('token' => $auth->create_token($user_id));
                    } else {
                        $return_errors['incorrect_fields'] = 'username';
                    }
                } else {
                    $return_errors['incorrect_fields'] = 'email';
                }
            } else {
                $return_errors['already_exists'] = $used_values;
            }
        } else {
            $return_errors['empty_fields'] = array_keys($v->errors());
        }
        return $this->create_error($return_errors);
    }


    /**
     * Check if a username is already used
     *
     * @param string $username Username to check
     * @return mixed User id if the user exists, otherwise false
     */
    public function get_user_id($username)
    {
        $sql = "SELECT id FROM user WHERE username = :username";
        $query = $this->db->prepare($sql);
        $parameters = array(':username' => $username);
        $query->execute($parameters);
        if ($query->rowCount() > 0) {
            return $query->fetch()->id;
        } else {
            return false;
        }
    }

    /**
     * Check if an email address is already used
     *
     * @param string $email Email address to check
     * @return bool True if the email address is already used
     */
    private function email_used($email)
    {
        $sql = "SELECT count(*) AS users FROM user WHERE email = :email";
        $query = $this->db->prepare($sql);
        $parameters = array(':email' => $email);
        $query->execute($parameters);
        $result = $query->fetch();
        return $result->users != 0;
    }


    /**
     * Authenticates the user
     * @param array $params Required parameters are username and password
     * @return array A token is returned on successful login, else 36003 is returned
     */
    public function auth($params)
    {
        $v = new Valitron\Validator($params);
        $v->rule('required', ['username', 'password']);
        if ($v->validate()) {
            $params = $this->filter_parameters($params, array('username', 'password'));
            $sql = "SELECT id, password FROM user WHERE username = :username";
            $query = $this->db->prepare($sql);
            $parameters = array(':username' => $params['username']);
            $query->execute($parameters);
            $result = $query->fetch();
            if (!$query->rowCount() > 0 || !validate_password($params['password'], $result->password)) {
                return $this->login_error();
            }
            $get_company_query = "SELECT id_company FROM company_has_user AS CU INNER JOIN company as C ON CU.id_company = C.id WHERE CU.id_user = :id_user AND C.deleted_at IS NULL";
            $get_company = $this->db->prepare($get_company_query);
            $get_company->execute([':id_user' => $result->id]);
            $company = $get_company->fetch();
            if($get_company->rowCount() <= 0) {
                return array('token' => $this->token->create_token($result->id), 'company' => -1);
            }

            return array('token' => $this->token->create_token($result->id, $company->id_company), 'company' => $company->id_company);
        } else {
            return $this->param_error();
        }
    }

    /**
     * Get all the user information
     * @param array $params Token is required
     */
    public function read($params)
    {
        $v = new Valitron\Validator($params);
        $v->rule('required', 'token');

        if ($v->validate()) {
            if (($user = $this->token->validate($params['token'])) !== false) {
                $sql = 'SELECT id, username, first_name, last_name, email, language FROM user WHERE id = :userid';
                $query = $this->db->prepare($sql);
                $parameters = array(':userid' => $user['id_user']);
                $query->execute($parameters);
                $result = $query->fetch();
                return array(
                    'username' => $result->username,
                    'email' => $result->email,
                    'first_name' => $result->first_name,
                    'last_name' => $result->last_name,
                    'language' => $result->language,
                    'last_active' => $user['last_active']
                );
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


    /**
     * Gets all the companies this user it connected to
     * @param $params
     */
    public function get_all_companies($params)
    {
        $v = new Valitron\Validator($params);
        $v->rule('required', 'token');

        if ($v->validate()) {
            if (($token = $this->token->validate($params['token'])) !== false) {
                $sql = 'SELECT cu.id_company, c.name FROM company_has_user as cu INNER JOIN company as c ON c.id = cu.id_company WHERE cu.id_user= :userid AND c.deleted_at IS NULL';
                $query = $this->db->prepare($sql);
                $parameters = array(':userid' => $token['id_user']);
                $query->execute($parameters);
                $result = $query->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } else {
                return $this->auth_error();
            }
        } else {
            return $this->param_error();
        }
    }

} 