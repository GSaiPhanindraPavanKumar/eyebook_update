<?php
namespace Models;

use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
require_once __DIR__ . '/../vendor/autoload.php';

class Student {
    public static function getCount($conn) {
        $sql = "SELECT COUNT(*) as student_count FROM students";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['student_count'] ?? 0;
    }

    public static function addStudentsFromExcel($conn, $filePath, $university_id) {
        // Enable error reporting
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        // Set PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $header = $sheet->rangeToArray('A1:Z1', null, true, true, true)[1] ?? null; // Assuming the first row is the header

            if ($header === null || empty($header)) {
                throw new \Exception("Invalid file format: Header row is missing or empty.");
            }

            foreach ($sheet->getRowIterator(2) as $row) {
                $rowData = $sheet->rangeToArray('A' . $row->getRowIndex() . ':Z' . $row->getRowIndex(), null, true, true, true)[1] ?? null;
                if ($rowData === null || empty($rowData)) {
                    continue; // Skip empty rows
                }
                $data = array_combine($header, $rowData);
                if ($data === false) {
                    throw new \Exception("Invalid file format: Data row does not match header columns.");
                }

                // Check for duplicates
                $checkSql = "SELECT COUNT(*) FROM students WHERE regd_no = :regd_no OR email = :email";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->execute([
                    ':regd_no' => $data['regd_no'],
                    ':email' => $data['email']
                ]);
                $count = $checkStmt->fetchColumn();

                if ($count > 0) {
                    continue; // Skip duplicates
                }

                $sql = "INSERT INTO students (regd_no, name, email, section, stream, year, dept, university_id, password) VALUES (:regd_no, :name, :email, :section, :stream, :year, :dept, :university_id, :password)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':regd_no' => $data['regd_no'],
                    ':name' => $data['name'],
                    ':email' => $data['email'],
                    ':section' => $data['section'],
                    ':stream' => $data['stream'],
                    ':year' => $data['year'],
                    ':dept' => $data['dept'],
                    ':university_id' => $university_id,
                    ':password' => password_hash($data['password'], PASSWORD_BCRYPT)
                ]);

                // Log the executed SQL statement and data
                error_log("Executed SQL: " . $sql);
                error_log("Data: " . json_encode([
                    ':regd_no' => $data['regd_no'],
                    ':name' => $data['name'],
                    ':email' => $data['email'],
                    ':section' => $data['section'],
                    ':stream' => $data['stream'],
                    ':year' => $data['year'],
                    ':dept' => $data['dept'],
                    ':university_id' => $university_id,
                    ':password' => password_hash($data['password'], PASSWORD_BCRYPT)
                ]));
            }
        } catch (\Exception $e) {
            error_log("Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function login($username, $password) {
        $conn = Database::getConnection();
        $sql = "SELECT * FROM students WHERE email = :username";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':username' => $username]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($student && password_verify($password, $student['password'])) {
            return $student;
        }

        return false;
    }
}