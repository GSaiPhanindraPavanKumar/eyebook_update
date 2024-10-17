<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

use Bramus\Router\Router;

// echo "Router is running"; // Debugging output

$router = new Router();

// Define routes
$router->get('/', function() {
    require __DIR__ . '/views/index.php';
});

// Add more routes here
$router->set404(function() {
    header('HTTP/1.1 404 Not Founds');
    echo "<h1>404 Not Found</h1>";
    echo "<p>The page you are looking for does not exist.</p>";
});

// Run the router
$router->run();
?>