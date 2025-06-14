<?php
require_once __DIR__ . "/BaseDao.php";

class CartDao extends BaseDao {
    public function __construct()
    {
        parent::__construct('cart');
    }

public function add_to_cart($user_id, $product_id, $quantity = 1)
{
    $cart_item = $this->query_unique(
        "SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id",
        ["user_id" => $user_id, "product_id" => $product_id]
    );

    if ($cart_item) {
        // Dodaj na postojeću količinu
        $new_quantity = $cart_item['quantity'] + $quantity;
        $this->update_quantity($user_id, $product_id, $new_quantity);
    } else {
        // Unesi novi proizvod u korpu
        $this->insert("cart", [
            "user_id" => $user_id,
            "product_id" => $product_id,
            "quantity" => $quantity
        ]);
    }

    return ["status" => "success", "message" => "Item added to cart"];
}


    public function remove_from_cart($user_id, $product_id)
    {
        $query = "DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id";
        $this->query($query, [
            "user_id" => $user_id,
            "product_id" => $product_id
        ]);
    }

    public function update_quantity($user_id, $product_id, $quantity)
    {
        $query = "UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id";
        $this->query($query, [
            "quantity" => $quantity,
            "user_id" => $user_id,
            "product_id" => $product_id
        ]);
    }

    public function get_cart_by_user($user_id, $search = "", $sort_by = "name", $sort_order = "asc")
    {
        $allowed_sort_columns = ["name", "price_each"];
        $allowed_sort_order = ["asc", "desc"];

        if (!in_array(strtolower($sort_by), $allowed_sort_columns)) {
            $sort_by = "name";
        }

        if (!in_array(strtolower($sort_order), $allowed_sort_order)) {
            $sort_order = "asc";
        }

        $query = "SELECT 
                    p.id AS product_id,
                    p.name,
                    p.category_id,
                    p.price_each AS price,
                    p.description,
                    c.quantity AS cart_quantity
                FROM cart c
                JOIN product p ON c.product_id = p.id
                WHERE c.user_id = :user_id";

        $params = ["user_id" => $user_id];

        if (!empty($search)) {
            $query .= " AND LOWER(p.name) LIKE :search";
            $params["search"] = "%" . strtolower($search) . "%";
        }

        $query .= " ORDER BY $sort_by $sort_order";

        return $this->query($query, $params);
    }






    public function clear_cart($user_id)
        {
            $this->query("DELETE FROM cart WHERE user_id = :user_id", ["user_id" => $user_id]);
        }
    
    public function get_cart_summary_by_user($user_id)
        {
            $query = "SELECT 
                        SUM(c.quantity * p.price_each) AS total_value,
                        COUNT(*) AS total_count
                      FROM cart c
                      JOIN product p ON c.product_id = p.id
                      WHERE c.user_id = :user_id";
        
            $params = ["user_id" => $user_id];
        
            $result = $this->query($query, $params);
        
            return [
                "total_value" => $result[0]['total_value'] ?? 0, 
                "total_count" => $result[0]['total_count'] ?? 0
            ];
        }    


}