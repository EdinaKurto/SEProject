<?php
require_once __DIR__ . "/BaseDao.php";

class ItemInOrderDao extends BaseDao {
    public function __construct()
    {
        parent::__construct('item_in_order');
    }

    public function add_item_in_order($order_id, $product_id, $quantity)
    {
        $item_in_order_data = [
            "order_id" => $order_id,
            "product_id" => $product_id,
            "quantity" => $quantity
        ];

        return $this->insert('item_in_order', $item_in_order_data);
    }
}