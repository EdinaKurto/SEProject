<?php
require_once __DIR__ . "/../dao/ProductDao.php";

class ProductService {
    private $productDao;
    public function __construct() {
        $this->productDao = new productDao();
    }

    public function add_product($product) {
        if (empty($product)) {
            return "Invalid input";
        }
        return $this->productDao->add_product($product);
    }

    public function get_product_by_id($id) {
    $product = $this->productDao->get_product_by_id($id);
    $product['images'] = $this->productDao->get_images_by_product_id($id);
    return $product;
}


    public function get_all_products($search = null, $sort = null, $min_price = null, $max_price = null, $category_id = null) {
        $products = $this->productDao->get_all_products($search, $sort, $min_price, $max_price, $category_id);

        foreach ($products as &$product) {
            $product['images'] = $this->productDao->get_images_by_product_id($product['id']);
        }

        return $products;
    }

    public function product_exists($product_id) {
    return $this->productDao->get_product_by_id($product_id) !== null;
}



    public function update_product($product_id, $product) {
        if (empty($product_id) || empty($product)) {
            return "Invalid input";
        }
        return $this->productDao->update_product($product_id, $product);
    }

    public function delete_product($product_id) {
        if (empty($product_id)) {
            return "Invalid input";
        }
        $this->productDao->delete_product($product_id);
    }

    public function add_product_image($data) {
    return $this->productDao->insert('product_image', $data);
}

public function get_images_by_product_id($product_id) {
    return $this->productDao->get_images_by_product_id($product_id);
}

public function delete_product_image($image_id) {
    return $this->productDao->delete_product_image($image_id);
}
}