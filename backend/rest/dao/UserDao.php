<?php
require_once __DIR__ . "/BaseDao.php";
class UserDao extends BaseDao {
    public function __construct()
    {
        parent::__construct('user');
    }

    public function add_user($user)
    {
        return $this->insert('user', $user);
}
    public function get_user_by_id($user_id){
        return $this->query_unique("SELECT * FROM user WHERE id = :id", ["id" => $user_id]);
    }

    public function update_user($user_id, $user) {
        return $this->update("user", $user_id, $user);
    }

    public function delete_user($user_id) {
        $this->delete("user", $user_id);
    }
}