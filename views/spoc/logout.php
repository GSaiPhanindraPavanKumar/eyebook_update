<?php


// Destroy the session
session_destroy();

// Regenerate session ID
session_regenerate_id(true);

// Redirect to the login page with cache control headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: -1");

// Clear and remove any existing session data
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Redirect to the login page
header("Location: login?timestamp=" . time());
exit();
?>