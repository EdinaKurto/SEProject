<?php
require_once __DIR__ . "/BaseDao.php";

class ProductViewDao extends BaseDao {
    public function __construct()
    {
        parent::__construct('product_view');
    }

    public function insertOrUpdateProductView($customer_id, $product_id, $time) {

        $query = "SELECT COUNT(*) FROM product_view WHERE customer_id = :customer_id AND product_id = :product_id";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(['customer_id' => $customer_id, 'product_id' => $product_id]);
        $exists = $stmt->fetchColumn();

        if ($exists) {

            $query = "UPDATE product_view SET time = :time WHERE customer_id = :customer_id AND product_id = :product_id";
            $stmt = $this->connection->prepare($query);
            $stmt->execute(['customer_id' => $customer_id, 'product_id' => $product_id, 'time' => $time]);
            return ['message' => 'Product view updated successfully'];
        } else {

            $query = "INSERT INTO product_view (customer_id, product_id, time) VALUES (:customer_id, :product_id, :time)";
            $stmt = $this->connection->prepare($query);
            $stmt->execute(['customer_id' => $customer_id, 'product_id' => $product_id, 'time' => $time]);
            return ['message' => 'Product view inserted successfully'];
        }
    }

    public function getUserProductViews($user_id) {
        $query = "
            SELECT 
                pv.customer_id,
                u.name AS customer_name, 
                pv.product_id,
                p.name AS product_name, 
                pv.time
            FROM product_view pv
            JOIN user u ON pv.customer_id = u.id
            JOIN product p ON pv.product_id = p.id
            WHERE pv.customer_id = :user_id
            ORDER BY pv.time DESC
        ";

        $stmt = $this->connection->prepare($query);
        $stmt->execute(["user_id" => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }




}