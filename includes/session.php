<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isFaculty() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'faculty';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireFaculty() {
    requireLogin();
    if (!isFaculty()) {
        header('Location: index.php');
        exit();
    }
}