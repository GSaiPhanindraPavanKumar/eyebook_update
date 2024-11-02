<?php

use PHPUnit\Framework\TestCase;
use Controllers\AuthController;
use Models\Admin;

class AuthControllerTest extends TestCase
{
    protected $authController;

    protected function setUp(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function testLoginWithValidCredentials()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = 'admin';
        $_POST['password'] = 'password';

        $mockAdmin = $this->createMock(Admin::class);
        $mockAdmin->method('login')->willReturn(['username' => 'admin']);

        // Inject the mock into the AuthController
        $this->authController = new AuthController($mockAdmin);

        $this->authController->login();
        $this->assertEquals('admin', $_SESSION['admin']['username']);
    }

    public function testLoginWithInvalidCredentials()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = 'admin';
        $_POST['password'] = 'wrongpassword';

        $mockAdmin = $this->createMock(Admin::class);
        $mockAdmin->method('login')->willReturn(false);

        // Inject the mock into the AuthController
        $this->authController = new AuthController($mockAdmin);

        $this->expectOutputString('Invalid username or password');
        $this->authController->login();
    }

    public function testLoginWithGetRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->authController = new AuthController();

        $this->expectOutputRegex('/<html.*<\/html>/s');
        $this->authController->login();
    }
    // public function testLogout()
    // {
    //     $_SESSION['admin'] = ['username' => 'admin'];
    //     $this->authController->logout();
    //     $this->assertArrayNotHasKey('admin', $_SESSION);
    // }
}