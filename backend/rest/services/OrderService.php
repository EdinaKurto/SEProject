<?php
require_once __DIR__ . "/../dao/OrderDao.php";
require_once __DIR__ . "/../dao/OrderStatusDao.php";
require_once __DIR__ . "/../dao/ItemInOrderDao.php";
require_once __DIR__ . "/../dao/CartDao.php";

class OrderService {
    private $orderDao;
    private $orderStatusDao;
    private $itemInOrderDao;
    private $cartDao;

    public function __construct()
    {
        $this->orderDao = new OrderDao();
        $this->orderStatusDao = new OrderStatusDao();
        $this->itemInOrderDao = new ItemInOrderDao();
        $this->cartDao = new CartDao();
    }

    public function add_order($user_id, $order)
{
    if (empty($user_id)) return "Server error";
    if (empty($order)) return "Invalid input";

    // Step 1: Create the order
    $insert_result = $this->orderDao->add_order($order, $user_id);
$order_id = is_array($insert_result) ? $insert_result['id'] : $insert_result;


    // Step 2: Get items from cart
    $cart_items = $this->cartDao->get_cart_by_user($user_id);

    // Step 3: Insert each item into item_in_order
    foreach ($cart_items as $item) {
        $this->itemInOrderDao->add_item_in_order(
            $order_id,
            $item['product_id'],
            $item['cart_quantity']
        );
    }

    return ["status" => "success", "message" => "Order placed successfully"];
}


    public function get_orders_by_user($user_id)
    {
        if (empty($user_id)) return "Server error";
        return $this->orderDao->get_orders_by_user($user_id);
    }

    public function count_pending_orders($user_id)
    {
        if (empty($user_id)) return "Server error";
        return $this->orderDao->count_pending_orders($user_id);
    }

    public function count_delivered_orders($user_id)
    {
        if (empty($user_id)) return "Server error";
        return $this->orderDao->count_delivered_orders($user_id);
    }

    public function count_total_orders($user_id)
    {
        if (empty($user_id)) return "Server error";
        return $this->orderDao->count_total_orders($user_id);
    }

    public function update_order_status($order_id, $new_status_id)
    {
        if (empty($order_id) || empty($new_status_id)) return "Invalid input";
        return $this->orderDao->update_order_status($order_id, $new_status_id);
    }

    public function delete_order($order_id)
    {
        if (empty($order_id)) return "Invalid input";
        return $this->orderDao->delete_order($order_id);
    }

    public function get_all_orders() {
    return $this->orderDao->get_all_orders();
}
public function get_order_statuses()
    {
        return $this->orderStatusDao->get_order_statuses();
    }

    public function get_status_by_id($status_id) {
        return $this->orderStatusDao->get_status_by_id($status_id);
    }


}