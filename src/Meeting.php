<?php
namespace Ramakrishnareddy\MeetingLms;

class Meeting
{
    private $id;
    private $title;
    private $section;
    private $facultyId;
    private $facultyName;
    private $roomId;
    private $roomUrl;

    public function __construct($title, $section, $facultyId, $facultyName, $roomId, $roomUrl)
    {
        $this->title = $title;
        $this->section = $section;
        $this->facultyId = $facultyId;
        $this->facultyName = $facultyName;
        $this->roomId = $roomId;
        $this->roomUrl = $roomUrl;
    }

    // Add getters and setters as needed
}