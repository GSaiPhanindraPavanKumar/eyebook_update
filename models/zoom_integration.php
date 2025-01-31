<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Include Composer's autoloader
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/config.php';
use Models\Database;

use GuzzleHttp\Client;

class ZoomAPI {
    private $clientId;
    private $clientSecret;
    private $accountId;
    private $accessToken;
    private $client;
    private $conn;

    public function __construct($clientId, $clientSecret, $accountId, $conn) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->accountId = $accountId;
        $this->client = new Client(['base_uri' => 'https://zoom.us/']);
        $this->conn = $conn;
        $this->accessToken = $this->generateAccessToken();
    }

    private function generateAccessToken() {
        $response = $this->client->request('POST', 'oauth/token', [
            'auth' => [$this->clientId, $this->clientSecret],
            'form_params' => [
                'grant_type' => 'account_credentials',
                'account_id' => $this->accountId
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['access_token'];
    }

    public function createVirtualClassroom($topic, $start_time, $duration) {
        $response = $this->client->request('POST', 'v2/users/me/meetings', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'topic' => $topic,
                'type' => 2,
                'start_time' => $start_time,
                'duration' => $duration,
                'timezone' => 'UTC',
                'settings' => [
                    'host_video' => true,
                    'participant_video' => true,
                    'join_before_host' => true,
                    'mute_upon_entry' => true,
                    'waiting_room' => true
                ]
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        return $data;
    }
    public function saveVirtualClassroom($topic, $start_time, $duration, $join_url, $selectedCourses) {
        // Save the start time and course IDs with the classroom in the correct format
        $stmt = $this->conn->prepare("INSERT INTO virtual_classrooms (topic, start_time, duration, join_url, course_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$topic, $start_time, $duration, $join_url, json_encode($selectedCourses)]);
        return $this->conn->lastInsertId();
    }

    public function saveVirtualClassroomToDatabase($data, $selectedCourses, $start_time_local) {
        // Use the provided local start time
        $start_time = $start_time_local;
    
        // Check if the classroom already exists
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM virtual_classrooms WHERE classroom_id = ?");
        $stmt->execute([$data['id']]);
        $count = $stmt->fetchColumn();
    
        if ($count == 0) {
            $stmt = $this->conn->prepare("INSERT INTO virtual_classrooms (classroom_id, topic, start_time, duration, join_url, course_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['id'], $data['topic'], $start_time, $data['duration'], $data['join_url'], json_encode($selectedCourses)]);
            return $this->conn->lastInsertId(); // Return the ID of the newly created virtual class
        }
    
        return null;
    }

    public function getAllClassrooms() {
        $stmt = $this->conn->query("SELECT * FROM virtual_classrooms ORDER BY start_time DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAttendance($classroomId) {
        $stmt = $this->conn->prepare("SELECT * FROM attendance WHERE classroom_id = ?");
        $stmt->execute([$classroomId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Database connection
$conn = Database::getConnection();
$zoomAPI = new ZoomAPI(ZOOM_CLIENT_ID, ZOOM_CLIENT_SECRET, ZOOM_ACCOUNT_ID, $conn);