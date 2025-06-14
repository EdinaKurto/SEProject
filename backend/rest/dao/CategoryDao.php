<?php
require_once __DIR__ . "/BaseDao.php";

class CategoryDao extends BaseDao {
    public function __construct()
    {
        parent::__construct('category');
    }


    public function getCategories() {
        $query = "
            SELECT 
                id,
                name
            FROM category
        ";

        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_category_by_name($name) {
        return $this->query_unique("SELECT * FROM category WHERE name = :name", ["name" => $name]); 
    }

    public function get_category_by_id($id) {
    return $this->query_unique("SELECT * FROM category WHERE id = :id", ["id" => $id]);
}

    





}