<?php
require_once __DIR__ . "/BaseDao.php";
class AuthDao extends BaseDao {
    public function __construct() {
               // Log which table is being used for debugging
        error_log('AuthDao constructed with table: users');
        parent::__construct("users");
    }

    public function get_user_by_email($email) {
        $query = "SELECT *
                  FROM user
                  WHERE email = :email";
        return $this->query_unique($query, ['email' => $email]);
    }
}