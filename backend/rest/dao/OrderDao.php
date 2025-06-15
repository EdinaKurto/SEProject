<?php
require_once __DIR__ . "/BaseDao.php";
class OrderDao extends BaseDao {
    public function __construct()
    {
        parent::__construct('order');
    }

    public function add_order($order, $user_id)
    {
        $order_data = [
            "user_id"      => $user_id, 
            "name"         => $order["name"],
            "surname"      => $order["surname"],
            "address"      => $order["address"],
            "city"         => $order["city"],
            "country"      => $order["country"],
            "phone_number" => $order["phone_number"],
            "date"         => date("Y-m-d H:i:s"),
            "status_id"    => 2
        ];

        return $this->insert('`order`', $order_data);
    }

    public function get_orders_by_user($user_id) {
        $query = "
            SELECT 
                o.id AS order_id,
                o.date AS order_date,
                GROUP_CONCAT(p.name ORDER BY op.product_id) AS product_names,
                GROUP_CONCAT(op.quantity ORDER BY op.product_id) AS quantities,
                SUM(op.quantity * p.price_each) AS total_price,
                s.name AS status_name
            FROM `order` o
            JOIN `item_in_order` op ON o.id = op.order_id
            JOIN `product` p ON op.product_id = p.id
            JOIN `status` s ON o.status_id = s.id
            WHERE o.user_id = :user_id
            GROUP BY o.id, o.date, s.name
        ";
    
        $params = ['user_id' => $user_id];
    
        return $this->query($query, $params);
    }
    
    public function get_all_orders() {
    $query = "
        SELECT 
                o.id AS order_id,
                o.date AS order_date,
                GROUP_CONCAT(p.name ORDER BY op.product_id) AS product_names,
                GROUP_CONCAT(op.quantity ORDER BY op.product_id) AS quantities,
                SUM(op.quantity * p.price_each) AS total_price,
                s.name AS status_name
            FROM `order` o
            JOIN `item_in_order` op ON o.id = op.order_id
            JOIN `product` p ON op.product_id = p.id
            JOIN `status` s ON o.status_id = s.id
            GROUP BY o.id, o.date, s.name
    ";

    return $this->query($query, []);
}

    public function count_pending_orders($user_id) {
        $query = "
            SELECT COUNT(*) AS pending_order_count
            FROM `order` o
            JOIN `status` s ON o.status_id = s.id
            WHERE o.user_id = :user_id AND s.name = 'Pending'
        ";
    
        $params = ['user_id' => $user_id];
    
        $result = $this->query($query, $params);
        return $result[0]['pending_order_count'];
    }

    public function count_delivered_orders($user_id) {
        $query = "
            SELECT COUNT(*) AS delivered_order_count
            FROM `order` o
            JOIN `status` s ON o.status_id = s.id
            WHERE o.user_id = :user_id AND s.name = 'Delivered'
        ";
    
        $params = ['user_id' => $user_id];
    
        $result = $this->query($query, $params);
        return $result[0]['delivered_order_count'];
    }
    
    public function count_total_orders($user_id) {
        $query = "
            SELECT COUNT(*) AS total_order_count
            FROM `order` o
            WHERE o.user_id = :user_id
        ";
    
        $params = ['user_id' => $user_id];
    
        $result = $this->query($query, $params);
        return $result[0]['total_order_count'];
    }    
    public function update_order_status($order_id, $new_status_id) {
        return $this->query_unique(
            "UPDATE `order` SET status_id = :status_id WHERE id = :order_id", 
            [
                'status_id' => is_array($new_status_id) ? reset($new_status_id) : $new_status_id,
                'order_id' => $order_id
            ]
        );
    }          
    public function delete_order($order_id) {
        return $this->query_unique("DELETE FROM `order` WHERE id = :order_id", ["order_id" => $order_id]);
    }    
}