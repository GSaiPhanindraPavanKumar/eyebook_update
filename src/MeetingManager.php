<?php
namespace Ramakrishnareddy\MeetingLms;

use GuzzleHttp\Client;

class MeetingManager
{
    private $client;

    public function __construct($apiKey)
    {
        $this->client = new Client([
            'base_uri' => 'https://api.daily.co/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    private function generateValidRoomName($title)
    {
        // Remove any non-alphanumeric characters and replace spaces with underscores
        $validName = preg_replace('/[^a-zA-Z0-9]+/', '_', $title);
        // Ensure the name starts with a letter
        $validName = 'room_' . $validName;
        // Truncate to 64 characters (Daily.co limit)
        $validName = substr($validName, 0, 64);
        // Append a unique identifier
        return $validName . '_' . uniqid();
    }

    // Add more methods as needed
}
