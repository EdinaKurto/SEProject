<?php
 
 require_once __DIR__ . "/../dao/ProductViewDao.php";
 
 class ProductViewService{
     private $productViewDao;
 
     public function __construct()
     {
         $this->productViewDao = new ProductViewDao();
     }
 
     public function addOrUpdateProductView($customer_id, $product_id, $time) {

        if (empty($product_id) || empty($time)) return "Invalid input";
        if (empty($customer_id)) return "Server error";

         return $this->productViewDao->insertOrUpdateProductView($customer_id, $product_id, $time);
     }
 
     public function getUserProductViews($user_id) {
        if (empty($user_id)) return "Server error";

         return $this->productViewDao->getUserProductViews($user_id);
     }
 }