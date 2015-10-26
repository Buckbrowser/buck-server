<?php

/**
 * Created by PhpStorm.
 * User: langstra
 * Date: 17-12-14
 * Time: 11:01
 */
class Template extends Model
{


    /**
     * Includes passwordhash for creating and verifying password
     */
    function __construct($db)
    {
        parent::__construct($db);
    }


    /**
     * @param array $params Parameters for creating a template
     *                      token, name, content
     * @return array
     */
    public function create($params)
    {
        $params = $this->filter_parameters($params, array('token', 'name', 'content'));
        $v = new \Valitron\Validator($params);
        $v->rules([
                'required' => [['token'], ['name'], ['content']]
            ]
        );

        $return_errors = null;
        if ($v->validate()) {
            if (($token = $this->token->validate($params['token'])) !== false) {
                if ($v->validate()) {

                    unset($params['token']);
                    $sql = "INSERT INTO template (";
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

                    if (!$query->execute($params)) {
                        $this->db->rollBack();
                        return $this->what_error();
                    } else {

                        $this->db->commit();
                        return $this->return_true();

                    }
                } else {
                    $return_errors['incorrect_fields'] = 'email';
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
     * @param array $params Token and id of the template are required
     */
    public function read($params)
    {
        $v = new Valitron\Validator($params);
        $v->rule('required', ['token', 'id']);

        if ($v->validate()) {
            if (($token = $this->token->validate($params['token'])) !== false) {
                $sql = 'SELECT * FROM template WHERE id_company = :company_id AND id = :template_id';
                $query = $this->db->prepare($sql);
                $parameters = [':template_id' => $params['id'], ':company_id' => $token['id_company']];
                $query->execute($parameters);
                if ($query->rowCount() > 0) {
                    $result = $query->fetch();
                    return array(
                        'name' => $result->name,
                        'content' => $result->content
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
     * @param array $params Parameters for updating a template
     *                      token, id are required
     *                      name and content are optional
     * @return array
     */
    public function update($params)
    {
        $v = new Valitron\Validator($params);
        $v->rule('required', ['token', 'id']);

        if ($v->validate()) {
            if (($user = $this->token->validate($params['token'])) !== false) {
                if ($v->validate()) {
                    $template_id = $params['id'];
                    $params = $this->filter_parameters($params, array('name', 'content'));

                    $sql = 'UPDATE template SET';
                    foreach ($params as $key => $value) {
                        $sql .= ' ' . $key . ' = :' . $key . ',';
                        $parameters[':' . $key] = $value;
                    }
                    $sql = substr($sql, 0, -1);
                    $sql .= ' WHERE id = :template_id AND id_company = :company_id';
                    $parameters[':template_id'] = $template_id;
                    $parameters[':company_id'] = $user['id_company'];

                    $query = $this->db->prepare($sql);
                    $this->db->beginTransaction();

                    if ($query->execute($parameters)) {
                        $this->db->commit();
                        return $this->return_true();
                    } else {
                        $this->rollback();
                        $this->what_error();
                    }

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
     * Deletes contact of the company
     * @param $params - token and id of the contact are required
     * @return array - returns errors as an array of true when successfully deleted.
     */
    public function delete($params)
    {
        $v = new Valitron\Validator($params);
        $v->rule('required', ['token', 'id']);

        if ($v->validate()) {
            if (($user = $this->token->validate($params['token'])) !== false) {

                $sql = 'DELETE FROM template WHERE id = :template_id id_company = :company_id';
                $parameters = [':template_id' => $params['id'], ':company_id' => $user['id_company']];

                $query = $this->db->prepare($sql);
                $this->db->beginTransaction();

                if ($query->execute($parameters)) {
                    $this->db->commit();
                    return $this->return_true();
                } else {
                    $this->rollback();
                    $this->what_error();
                }

                return $this->return_true();
            } else {
                return $this->auth_error();
            }
        } else {
            return $this->param_error();
        }
    }

} 