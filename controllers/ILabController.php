<?php
namespace Controllers\Student;

class ILabController {
    public function index() {
        // Using DIRECTORY_SEP  ARATOR for cross-platform compatibility
        require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'i-Lab' . DIRECTORY_SEPARATOR . 'index.html';
        
        // Alternative using forward slashes (PHP will convert automatically)
        // require_once __DIR__ . '/../i-Lab/index.html';
    }
}
