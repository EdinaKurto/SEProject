<?php
 
 require_once __DIR__ . "/../dao/CategoryDao.php";
 
 class CategoryService{
     private $categoryDao;
 
     public function __construct()
     {
         $this->categoryDao = new CategoryDao();
     }
     public function getCategories() {

         return $this->categoryDao->getCategories();
     }

    public function get_category_by_name($name) {
        if (empty($name)) return "Server error";
        return $this->categoryDao->get_category_by_name($name);
    }

    public function get_category_by_id($id) {
    if (empty($id) || !is_numeric($id) || intval($id) <= 0) {
        return null;
    }
    return $this->categoryDao->get_category_by_id($id);
}
}