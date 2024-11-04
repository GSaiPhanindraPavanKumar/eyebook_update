<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // Include Composer's autoloader
require_once 'config.php';
require_once '../../config/connection.php';

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
                'Content-Type' => 'application/json'
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
                    'join_before_host' => false,
                    'mute_upon_entry' => true,
                    'waiting_room' => true
                ]
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        $this->saveVirtualClassroomToDatabase($data);
        return $data;
    }

    private function saveVirtualClassroomToDatabase($data) {
        $stmt = $this->conn->prepare("INSERT INTO virtual_classrooms (classroom_id, topic, start_time, duration, join_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $data['id'], $data['topic'], $data['start_time'], $data['duration'], $data['join_url']);
        $stmt->execute();
    }

    public function getAllClassrooms() {
        $result = $this->conn->query("SELECT * FROM virtual_classrooms ORDER BY start_time DESC");
        $classrooms = [];
        while ($row = $result->fetch_assoc()) {
            $classrooms[] = $row;
        }
        return $classrooms;
    }

    public function getAttendance($classroomId) {
        $stmt = $this->conn->prepare("SELECT * FROM attendance WHERE classroom_id = ?");
        $stmt->bind_param("s", $classroomId);
        $stmt->execute();
        $result = $stmt->get_result();
        $attendance = [];
        while ($row = $result->fetch_assoc()) {
            $attendance[] = $row;
        }
        return $attendance;
    }
}

// Database connection
$zoomAPI = new ZoomAPI(ZOOM_CLIENT_ID, ZOOM_CLIENT_SECRET, ZOOM_ACCOUNT_ID, $conn);
?>