<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic = $_POST['topic'];
    $numQuestions = intval($_POST['numQuestions']);
    $marksPerQuestion = intval($_POST['marksPerQuestion']);

    try {
        $questions = generateQuestions($topic, $numQuestions, $marksPerQuestion);
        echo json_encode($questions);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}