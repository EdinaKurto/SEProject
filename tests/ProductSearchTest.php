<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../backend/rest/services/ProductsService.php';

class ProductSearchTest extends TestCase {
    private $productsService;

    protected function setUp(): void {
        $this->productsService = new ProductsService();
    }

    public function testSearchProductsByName() {
        $name = 'Test Product'; // Ensure this product exists in your test DB
        $result = $this->productsService->searchProductsByName($name);
        $this->assertIsArray($result);
    }

    public function testSearchByGenderAndNote() {
        $genderId = 1; // Use a valid gender ID from your test DB
        $noteName = 'Citrus'; // Use a valid note name from your test DB
        $result = $this->productsService->searchByGenderAndNote($genderId, $noteName);
        $this->assertIsArray($result);
    }
} 