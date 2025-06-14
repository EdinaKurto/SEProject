<?php
require_once __DIR__ . "/../dao/ItemInOrderDao.php";

class ItemInOrderService {
    private $orderDao;

    public function __construct()
    {
        $this->itemInOrderDao = new ItemInOrderDao();
    }

    public function add_item_in_order($order_id, $product_id, $quantity)
    {
        if (empty($product_id) || empty($order_id) || $quantity === null) return "Invalid input";

        return $this->itemInOrderDao->add_item_in_order($order_id, $product_id, $quantity);
    }
}