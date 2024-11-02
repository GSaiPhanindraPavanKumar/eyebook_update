<?php

use PHPUnit\Framework\TestCase;
use Controllers\AdminController;
use Models\University;
use Models\Spoc;
use Models\Database;

class AdminControllerTest extends TestCase
{
    protected $adminController;

    protected function setUp(): void
    {
        $this->adminController = new AdminController();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function testAddUniversityWithValidData()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['long_name'] = 'Test University';
        $_POST['short_name'] = 'TU';
        $_POST['location'] = 'Test Location';
        $_POST['country'] = 'Test Country';
        $_POST['spoc_name'] = 'Test Spoc';
        $_POST['spoc_email'] = 'spoc@test.com';
        $_POST['spoc_phone'] = '1234567890';
        $_POST['spoc_password'] = 'password';

        $mockDatabase = $this->createMock(Database::class);
        $mockDatabase->method('getConnection')->willReturn($this->createMock(PDO::class));

        $mockUniversity = $this->createMock(University::class);
        $mockUniversity->method('existsByShortName')->willReturn(false);
        $mockUniversity->method('addUniversity')->willReturn(['message' => 'University and SPOC added successfully', 'message_type' => 'success']);

        $mockSpoc = $this->createMock(Spoc::class);
        $mockSpoc->method('existsByEmail')->willReturn(false);

        // Inject the mock into the AdminController
        $this->adminController->database = $mockDatabase;
        $this->adminController->universityModel = $mockUniversity;
        $this->adminController->spocModel = $mockSpoc;

        ob_start();
        $this->adminController->addUniversity();
        $output = ob_get_clean();

        $this->assertStringContainsString('University and SPOC added successfully', $output);
    }

    public function testAddUniversityWithDuplicateShortName()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['long_name'] = 'Test University';
        $_POST['short_name'] = 'TU';
        $_POST['location'] = 'Test Location';
        $_POST['country'] = 'Test Country';
        $_POST['spoc_name'] = 'Test Spoc';
        $_POST['spoc_email'] = 'spoc@test.com';
        $_POST['spoc_phone'] = '1234567890';
        $_POST['spoc_password'] = 'password';

        $mockDatabase = $this->createMock(Database::class);
        $mockDatabase->method('getConnection')->willReturn($this->createMock(PDO::class));

        $mockUniversity = $this->createMock(University::class);
        $mockUniversity->method('existsByShortName')->willReturn(true);

        $mockSpoc = $this->createMock(Spoc::class);
        $mockSpoc->method('existsByEmail')->willReturn(false);

        // Inject the mock into the AdminController
        $this->adminController->database = $mockDatabase;
        $this->adminController->universityModel = $mockUniversity;
        $this->adminController->spocModel = $mockSpoc;

        ob_start();
        $this->adminController->addUniversity();
        $output = ob_get_clean();

        $this->assertStringContainsString('Duplicate entry for short name', $output);
    }

    public function testAddUniversityWithDuplicateSpocEmail()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['long_name'] = 'Test University';
        $_POST['short_name'] = 'TU';
        $_POST['location'] = 'Test Location';
        $_POST['country'] = 'Test Country';
        $_POST['spoc_name'] = 'Test Spoc';
        $_POST['spoc_email'] = 'spoc@test.com';
        $_POST['spoc_phone'] = '1234567890';
        $_POST['spoc_password'] = 'password';

        $mockDatabase = $this->createMock(Database::class);
        $mockDatabase->method('getConnection')->willReturn($this->createMock(PDO::class));

        $mockUniversity = $this->createMock(University::class);
        $mockUniversity->method('existsByShortName')->willReturn(false);

        $mockSpoc = $this->createMock(Spoc::class);
        $mockSpoc->method('existsByEmail')->willReturn(true);

        // Inject the mock into the AdminController
        $this->adminController->database = $mockDatabase;
        $this->adminController->universityModel = $mockUniversity;
        $this->adminController->spocModel = $mockSpoc;

        ob_start();
        $this->adminController->addUniversity();
        $output = ob_get_clean();

        $this->assertStringContainsString('Duplicate entry for email', $output);
    }
}