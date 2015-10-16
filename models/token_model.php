<?php
/**
 * Created by PhpStorm.
 * User: langstra
 * Date: 17-12-14
 * Time: 22:34
 */

class Token extends Model{

    public $db;

    function __construct($db)
    {
        $this->db = $db;
    }


    /**
     * Creates a token for the given user
     *
     * @param integer $user User id of the user for which a token must be generated
     * @param integer $company Company id of the company the user is accessing. This is optional.
     *
     * @return string Generated token for the user
     */
    public function create_token($user, $company = null)
    {
        $token = bin2hex(openssl_random_pseudo_bytes(64));
        $sql = "INSERT INTO token
                (id_user, id_company, token, last_active)
                VALUES (:user, :company, :token, CURRENT_TIMESTAMP)";
        $query = $this->db->prepare($sql);
        $params = array(':user' => $user, ':company' => $company, ':token' => $token);
        $query->execute($params);

        return $token;
    }


    /**
     * Updates the last active timestamp of a token
     *
     * @param string $token Token you want to update the last active of
     */
    public function update_token($token)
    {
        $sql = "UPDATE token
            SET last_active = CURRENT_TIMESTAMP
            WHERE token = :token";
        $params = array(':token' => $token);
        $query = $this->db->prepare($sql);
        $query->execute($params);
    }


    /**
     * @param $token token of the user to validate
     * @param int $auth_level = 3 Authorization needed to access the function, 3 is default and gives always access
     * @return array|bool
     */
    public function validate($token, $auth_level = 3)
    {
        $sql = "SELECT T.id_user, T.id_company, T.last_active
                FROM token as T INNER JOIN company AS C ON T.id_company = C.id
                  INNER JOIN company_has_user as CU ON C.id = CU.id_company
                  INNER JOIN user_roles AS UR ON CU.user_role = UR.id INNER JOIN role_has_api_access AS RHAA ON UR.id = RHAA.id_role
                  INNER JOIN api_access_parts AS AAP ON AAP.id = RHAA.id_access_part
                WHERE T.token = :token
                AND AAP.id = :authorization_level AND last_active > DATE_SUB(NOW(), INTERVAL 7 day) AND C.deleted_at IS NULL";
        $query = $this->db->prepare($sql);
        $params = array(':token' => $token, ':authorization_level' => $auth_level);
        $query->execute($params);

        if( $query->rowCount() > 0) {
            $result = $query->fetch();
            $this->update_token($token);
            return array('id_user' => $result->id_user, 'id_company' => $result->id_company, 'last_active' => $result->last_active);
        } else {
            return false;
        }
    }

    public function delete_token($token)
    {
        $sql = "DELETE FROM token WHERE token = :token";
        $query = $this->db->prepare($sql);
        $parameters = array(':token' => $token);
        $query->execute($parameters);
    }

}