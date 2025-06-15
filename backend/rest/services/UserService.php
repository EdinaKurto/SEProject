<?php
 require_once __DIR__ . "/../dao/UserDao.php";
class UserService{
    private $userDao;

    public function __construct()
    {
        $this->userDao = new UserDao();
    }

    public function add_user($user)
    {
        if (empty($user)) return "Invalid input";
        return $this->userDao->add_user($user);
    }

    public function get_user_by_id($user_id) {
        if (empty($user_id)) return "Server error";
        return $this->userDao->get_user_by_id($user_id);
    }

    public function update_user($user_id, $user) {
        if (empty($user_id)) return "Server error";
        if (empty($user)) return "Invalid input";

        return $this->userDao->update_user($user_id, $user);
    }

    public function delete_user($user_id) {
        if (empty($user_id)) return "Server error";
        $this->userDao->delete_user( $user_id);
    }
}