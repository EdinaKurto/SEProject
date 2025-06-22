<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../backend/rest/services/OrdersService.php';

class OrderStatusTest extends TestCase {
    private $ordersService;

    protected function setUp(): void {
        $this->ordersService = new OrdersService();
    }

    public function testUpdateOrderStatus() {
        $orderId = 1; // Use a valid order ID from your test DB
        $statusId = 2; // Use a valid status ID from your test DB
        $result = $this->ordersService->updateOrderStatus($orderId, $statusId);
        $this->assertTrue($result);
    }

    public function testGetOrdersByUserId() {
        $userId = 1; // Use a valid user ID from your test DB
        $result = $this->ordersService->getOrdersByUserId($userId);
        $this->assertIsArray($result);
    }
} 