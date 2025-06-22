<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../rest/services/UsersService.php';

class UsersServiceTest extends TestCase {
    private $usersService;

    protected function setUp(): void {
        $this->usersService = new UsersService();
    }

    public function testCreateUser() {
        $userData = [
            'user_name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'role_id' => 2
        ];
        $result = $this->usersService->createUser($userData);
        $this->assertNotEmpty($result, 'User creation failed.');
    }

    public function testGetUserById() {
        $userId = 1; // Assuming a user with ID 1 exists
        $user = $this->usersService->getUserById($userId);
        $this->assertNotEmpty($user, 'User not found.');
        $this->assertEquals($userId, $user['user_id'], 'User ID mismatch.');
    }
}