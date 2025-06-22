<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../rest/services/OrdersService.php';

class OrdersServiceTest extends TestCase {
    private $ordersService;

    protected function setUp(): void {
        $this->ordersService = new OrdersService();
    }

    public function testCreateOrder() {
        $orderData = [
            'user_id' => 1, // Assuming a user with ID 1 exists
            'status_id' => 1,
            'total_price' => 100.00
        ];
        $userId = 1; // Assuming a user with ID 1 exists
        $statusId = 1; // Assuming a status with ID 1 exists
        $result = $this->ordersService->createOrder($orderData, $userId, $statusId);
        $this->assertNotEmpty($result, 'Order creation failed.');
    }

    public function testGetOrderById() {
        $orderId = 1; // Assuming an order with ID 1 exists
        $order = $this->ordersService->getOrderById($orderId);
        $this->assertNotEmpty($order, 'Order not found.');
        $this->assertEquals($orderId, $order['order_id'], 'Order ID mismatch.');
    }
}