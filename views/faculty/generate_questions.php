<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic = $_POST['topic'] ?? '';
    $numQuestions = intval($_POST['numQuestions'] ?? 0);
    $marksPerQuestion = intval($_POST['marksPerQuestion'] ?? 0);

    try {
        $questions = generateQuestionsUsingGemini($topic, $numQuestions, $marksPerQuestion);
        header('Content-Type: application/json');
        echo json_encode($questions, JSON_THROW_ON_ERROR);
    } catch (Exception $e) {
        error_log('Error generating questions: ' . $e->getMessage());
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()], JSON_THROW_ON_ERROR);
    }
} else {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed'], JSON_THROW_ON_ERROR);
}