<?php
require_once __DIR__ . "/BaseDao.php";

class WishlistDao extends BaseDao {
    public function __construct()
    {
        parent::__construct('wishlist');
    }

public function add_to_wishlist($user_id, $product_id, $quantity = 1)
{
    $wishlist_item = $this->query_unique(
        "SELECT * FROM wishlist WHERE user_id = :user_id AND product_id = :product_id",
        ["user_id" => $user_id, "product_id" => $product_id]
    );

    if ($wishlist_item) {
        $new_quantity = $wishlist_item['quantity'] + $quantity;
        $this->update_quantity($user_id, $product_id, $new_quantity);
    } else {
        $this->insert("wishlist", [
            "user_id" => $user_id,
            "product_id" => $product_id,
            "quantity" => $quantity
        ]);
    }
}


    public function remove_from_wishlist($user_id, $product_id)
    {
        $query = "DELETE FROM wishlist WHERE user_id = :user_id AND product_id = :product_id";
        $this->query($query, [
            "user_id" => $user_id,
            "product_id" => $product_id
        ]);
    }

    public function update_quantity($user_id, $product_id, $quantity)
    {
        $query = "UPDATE wishlist SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id";
        $this->query($query, [
            "quantity" => $quantity,
            "user_id" => $user_id,
            "product_id" => $product_id
        ]);
    }

    public function get_wishlist_by_user($user_id, $search = "", $sort_by = "name", $sort_order = "asc")
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
                    w.quantity AS cart_quantity
                  FROM wishlist w
                  JOIN product p ON w.product_id = p.id
                  WHERE w.user_id = :user_id";
    
        $params = ["user_id" => $user_id];
    
        if (!empty($search)) {
            $query .= " AND LOWER(p.name) LIKE :search";
            $params["search"] = "%" . strtolower($search) . "%";
        }
    
        $query .= " ORDER BY $sort_by $sort_order";
    
        return $this->query($query, $params);
    }

    public function clear_wishlist($user_id)
    {
        $this->query("DELETE FROM wishlist WHERE user_id = :user_id", ["user_id" => $user_id]);
    }

    public function get_wishlist_summary_by_user($user_id)
    {
        $query = "SELECT 
                    SUM(w.quantity * p.price_each) AS total_value,
                    COUNT(*) AS total_count
                  FROM wishlist w
                  JOIN product p ON w.product_id = p.id
                  WHERE w.user_id = :user_id";
    
        $params = ["user_id" => $user_id];
    
        $result = $this->query($query, $params);
    
        return [
            "total_value" => $result[0]['total_value'] ?? 0, 
            "total_count" => $result[0]['total_count'] ?? 0
        ];
    } 
}