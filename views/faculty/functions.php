<?php
// File: functions.php
use Models\Database;
$conn = Database::getConnection();

function createAssessment($title, $questions, $deadline) {
    global $conn;
    
    $conn->beginTransaction();

    try {
        $stmt = $conn->prepare("INSERT INTO assessments (title, deadline) VALUES (?, ?)");
        $stmt->execute([$title, $deadline]);
        $assessmentId = $conn->lastInsertId();

        $stmt = $conn->prepare("INSERT INTO questions (assessment_id, question_text, options, correct_answer, marks) VALUES (?, ?, ?, ?, ?)");
        foreach ($questions as $question) {
            $options = json_encode($question['options']);
            $stmt->execute([$assessmentId, $question['questionText'], $options, $question['correctAnswer'], $question['marks']]);
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

function getAssessments() {
    global $conn;
    $stmt = $conn->query("SELECT * FROM assessments ORDER BY deadline DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAssessmentResults($assessmentId) {
    global $conn;
    $stmt = $conn->prepare("SELECT s.name as student_name, ar.score FROM assessment_results ar JOIN students s ON ar.student_id = s.id WHERE ar.assessment_id = ?");
    $stmt->execute([$assessmentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAvailableAssessments() {
    global $conn;
    $now = date('Y-m-d H:i:s');
    $stmt = $conn->query("SELECT * FROM assessments WHERE deadline > '$now' ORDER BY deadline ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAssessment($assessmentId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM assessments WHERE id = ?");
    $stmt->execute([$assessmentId]);
    $assessment = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT * FROM questions WHERE assessment_id = ?");
    $stmt->execute([$assessmentId]);
    $assessment['questions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $assessment;
}

function submitAssessment($assessmentId, $answers, $studentId) {
    global $conn;
    
    $score = 0;
    $totalMarks = 0;

    $stmt = $conn->prepare("SELECT * FROM questions WHERE assessment_id = ?");
    $stmt->execute([$assessmentId]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($questions as $index => $question) {
        $totalMarks += $question['marks'];
        if (isset($answers[$index]) && $answers[$index] === $question['correct_answer']) {
            $score += $question['marks'];
        }
    }

    // Store the result
    $stmt = $conn->prepare("INSERT INTO assessment_results (assessment_id, student_id, score) VALUES (?, ?, ?)");
    $stmt->execute([$assessmentId, $studentId, $score]);
    
    return $score;
}

function getOrCreateStudent($name, $email) {
    global $conn;
    
    // Check if student exists
    $stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
    $stmt->execute([$email]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student) {
        // Student exists, return their ID
        return $student['id'];
    } else {
        // Student doesn't exist, create new student
        $stmt = $conn->prepare("INSERT INTO students (name, email) VALUES (?, ?)");
        $stmt->execute([$name, $email]);
        return $conn->lastInsertId();
    }
}

// Add this new function for generating questions using Gemini API
function generateQuestionsUsingGemini($topic, $numQuestions, $marksPerQuestion) {
    $apiKey = 'AIzaSyAmfv5ML6txGnCXH3-7AYD-UwT57yj3VmI'; // Make sure this constant is defined in your config file
    $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    $prompt = "Generate {$numQuestions} multiple-choice questions about {$topic}. For each question, provide 4 options, indicate the correct answer, and assign {$marksPerQuestion} marks to each question. Format the response as a JSON array of objects, where each object has properties: questionText, options (an array of 4 strings), correctAnswer (the correct option string), and marks (an integer equal to {$marksPerQuestion}). Do not include any markdown formatting or additional text.";

    $data = [
        'contents' => [
            ['parts' => [['text' => $prompt]]]
        ]
    ];

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                "x-goog-api-key: $apiKey"
            ],
            'content' => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($apiUrl, false, $context);

    if ($result === FALSE) {
        throw new Exception('Failed to call Gemini API');
    }

    $response = json_decode($result, true);
    $generatedText = $response['candidates'][0]['content']['parts'][0]['text'];

    // Attempt to parse the entire response as JSON
    $questions = json_decode($generatedText, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $questions;
    }

    // If parsing fails, try to extract JSON from the response
    preg_match('/\[[\s\S]*\]/', $generatedText, $matches);
    if (empty($matches)) {
        throw new Exception('No valid JSON found in the response');
    }
    $jsonString = $matches[0];
    $questions = json_decode($jsonString, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Failed to parse generated questions');
    }

    return $questions;
}
?>