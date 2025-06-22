<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../rest/services/ProductsService.php';

class ProductsServiceTest extends TestCase {
    private $productsService;

    protected function setUp(): void {
        $this->productsService = new ProductsService();
    }

    public function testGetAllProducts() {
        $products = $this->productsService->getAllProducts();
        $this->assertNotEmpty($products, 'No products found.');
    }

    public function testSearchProductsByName() {
        $productName = 'Test Product'; // Assuming a product with this name exists
        $products = $this->productsService->searchProductsByName($productName);
        $this->assertNotEmpty($products, 'No products found with the given name.');
    }
}