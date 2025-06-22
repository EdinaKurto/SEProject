<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../backend/rest/services/UsersService.php';

class UserLoginTest extends TestCase {
    private $usersService;

    protected function setUp(): void {
        $this->usersService = new UsersService();
    }

    public function testLoginSuccess() {
        // You may need to ensure this user exists in your test DB
        $data = [
            'email' => 'testuser@example.com',
            'password' => 'password123'
        ];
        $result = $this->usersService->login($data);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('Login successful', $result['message']);
    }

    public function testLoginFailure() {
        $data = [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ];
        $result = $this->usersService->login($data);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Invalid email or password', $result['error']);
    }
} 