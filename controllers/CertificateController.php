<?php

namespace Controllers;

use Models\Database;
use Aws\S3\S3Client;
use ZipArchive;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
require_once 'aws_config.php';

class CertificateController {
    private $s3;
    
    public function __construct() {
        $this->s3 = new S3Client([
            'version' => 'latest',
            'region'  => AWS_REGION,
            'credentials' => [
                'key'    => AWS_ACCESS_KEY_ID,
                'secret' => AWS_SECRET_ACCESS_KEY,
            ],
        ]);

        // Create storage directory if it doesn't exist
        if (!file_exists('storage/certificates')) {
            mkdir('storage/certificates', 0777, true);
        }

        // Clean up old files (files older than 1 hour)
        $this->cleanupOldFiles('storage/certificates');
    }

    public function index() {
        $conn = Database::getConnection();
        
        // Get total count for pagination
        $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM certificate_generations");
        $countStmt->execute();
        $totalRecords = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];
        
        // Pagination settings
        $recordsPerPage = 10;
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($currentPage - 1) * $recordsPerPage;
        $totalPages = ceil($totalRecords / $recordsPerPage);
        
        // Get paginated records
        $stmt = $conn->prepare("SELECT * FROM certificate_generations ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $recordsPerPage, \PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $generations = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Get all records for client-side search
        $allStmt = $conn->prepare("SELECT * FROM certificate_generations ORDER BY created_at DESC");
        $allStmt->execute();
        $allGenerations = $allStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        require 'views/admin/certificate_generations.php';
    }

    public function create() {
        require 'views/admin/create_certificate_generation.php';
    }

    public function store() {
        try {
            error_log("[Certificate Generation] Starting certificate generation process");
            set_time_limit(600); // Increase timeout to 10 minutes
            $conn = Database::getConnection();
            
            // Validate files
            if (!isset($_FILES['template']) || !isset($_FILES['data_file'])) {
                error_log("[Certificate Generation] Missing required files");
                throw new \Exception('Missing required files');
            }

            // Create storage directories if they don't exist
            $storageDir = __DIR__ . '/../storage/';
            $tempDir = $storageDir . 'temp/';
            $certDir = $storageDir . 'certificates/';
            
            foreach ([$storageDir, $tempDir, $certDir] as $dir) {
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
            }

            // Save Excel file to temp storage
            $excelTempPath = $tempDir . uniqid() . '_' . $_FILES['data_file']['name'];
            if (!move_uploaded_file($_FILES['data_file']['tmp_name'], $excelTempPath)) {
                error_log("[Certificate Generation] Failed to move Excel file to: $excelTempPath");
                throw new \Exception("Failed to save Excel file");
            }
            error_log("[Certificate Generation] Excel file saved to: $excelTempPath");

            // Start transaction
            $conn->beginTransaction();
            
            // Save template locally first
            $templateFileName = uniqid() . '_' . $_FILES['template']['name'];
            $localTemplatePath = $storageDir . 'templates/' . $templateFileName;
            
            if (!file_exists(dirname($localTemplatePath))) {
                mkdir(dirname($localTemplatePath), 0777, true);
            }
            
            if (!move_uploaded_file($_FILES['template']['tmp_name'], $localTemplatePath)) {
                throw new \Exception("Failed to save template file");
            }
            
            // Upload template to S3
            $templateKey = 'templates/' . $templateFileName;
            $result = $this->s3->putObject([
                'Bucket' => AWS_BUCKET_NAME,
                'Key' => $templateKey,
                'SourceFile' => $localTemplatePath,
                'ACL' => 'private'
            ]);
            
            $templateUrl = $result['ObjectURL'];
            error_log("[Certificate Generation] Template uploaded to S3: $templateUrl");
            
            // Validate positions data
            if (!isset($_POST['positions'])) {
                throw new \Exception("Text positions data is required");
            }

            error_log("[Certificate Generation] Raw positions data: " . print_r($_POST['positions'], true));
            
            // Decode positions
            $positions = is_string($_POST['positions']) ? 
                json_decode($_POST['positions'], true) : 
                $_POST['positions'];

            if (!is_array($positions)) {
                throw new \Exception("Invalid positions format. Expected JSON object.");
            }

            // Validate required fields
            $requiredFields = ['registration_number', 'name', 'grade'];
            foreach ($requiredFields as $field) {
                if (!isset($positions[$field]) || 
                    !isset($positions[$field]['x']) || 
                    !isset($positions[$field]['y'])) {
                    error_log("[Certificate Generation] Missing coordinates for field: " . $field);
                    error_log("[Certificate Generation] Available positions: " . json_encode($positions));
                    throw new \Exception("Missing coordinates for {$field}. Please set all text positions.");
                }
            }

            // Normalize coordinates
            foreach ($positions as $field => &$pos) {
                $pos['x'] = (float)$pos['x'];
                $pos['y'] = (float)$pos['y'];
            }

            error_log("[Certificate Generation] Normalized positions: " . json_encode($positions));

            // Store the normalized positions
            $stmt = $conn->prepare("INSERT INTO certificate_generations (subject, template_path, template_type, text_positions, status, generated_count, total_count, excel_file_path, date_range) VALUES (?, ?, ?, ?, 'processing', 0, 0, ?, ?)");
            $stmt->execute([
                $_POST['subject'],
                $templateUrl,
                $_FILES['template']['type'],
                json_encode($positions),
                $excelTempPath,
                $_POST['date_range']
            ]);
            
            $generationId = $conn->lastInsertId();
            error_log("[Certificate Generation] Created generation record with ID: $generationId");
            
            // Get total count from Excel
            $totalCount = $this->getExcelRowCount($excelTempPath);
            error_log("[Certificate Generation] Total records to process: $totalCount");
            
            // Update total count
            $stmt = $conn->prepare("UPDATE certificate_generations SET total_count = ? WHERE id = ?");
            $stmt->execute([$totalCount, $generationId]);
            
            $conn->commit();
            
            // Don't start processing here, just redirect to progress page
            header('Location: /admin/certificate_generations/progress/' . $generationId);
            exit;
            
        } catch (\Exception $e) {
            error_log("[Certificate Generation] Error: " . $e->getMessage());
            if (isset($conn)) {
                $conn->rollBack();
            }
            $_SESSION['error'] = 'Failed to generate certificates: ' . $e->getMessage();
            header('Location: /admin/certificate_generations/create');
        }
    }

    public function progress($generationId) {
        try {
            error_log("[Certificate Progress] Viewing progress for ID: $generationId");
            $conn = Database::getConnection();
            $generation = $this->getGeneration($generationId);
            
            if (!$generation) {
                throw new \Exception('Generation not found');
            }
            
            if ($generation['status'] === 'completed') {
                header('Location: /admin/certificate_generations');
                exit;
            }
            
            // Just display the progress page
            require 'views/admin/certificate_generation_progress.php';
            
        } catch (\Exception $e) {
            error_log("[Certificate Progress] Error: " . $e->getMessage());
            $_SESSION['error'] = 'Error viewing progress: ' . $e->getMessage();
            header('Location: /admin/certificate_generations');
            exit;
        }
    }

    public function startGeneration($generationId) {
        try {
            // Clear any existing output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Get generation data first
            $generation = $this->getGeneration($generationId);
            if (!$generation) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Generation not found']);
                return;
            }

            // Send initial response
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Generation started']);
            
            // Close the connection to the client
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } else {
                ob_end_flush();
                flush();
            }
            
            // Start the generation process
            ignore_user_abort(true);
            set_time_limit(0);
            
            // Process certificates
            $this->processCertificatesWithProgress($generationId, $generation['excel_file_path']);
            
        } catch (\Exception $e) {
            error_log("[Certificate Generation] Error: " . $e->getMessage());
            if (!headers_sent()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    private function validateExcelHeaders($file) {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $headers = $worksheet->getRowIterator(1)->current();
        
        $requiredHeaders = ['SME', 'Name', 'Project Title'];
        $actualHeaders = [];
        
        foreach ($headers->getCellIterator() as $cell) {
            $actualHeaders[] = $cell->getValue();
        }
        
        return empty(array_diff($requiredHeaders, $actualHeaders));
    }

    private function processCertificatesWithProgress($generationId, $file) {
        try {
            $conn = Database::getConnection();
            $generation = $this->getGeneration($generationId);
            
            if (!$generation) {
                throw new \Exception("Generation not found");
            }

            // Update status to processing
            $stmt = $conn->prepare("UPDATE certificate_generations SET status = 'processing', progress = 0, generated_count = 0 WHERE id = ?");
            $stmt->execute([$generationId]);

            // Load Excel file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $worksheet = $spreadsheet->getActiveSheet();
            $totalRows = $worksheet->getHighestDataRow() - 1; // Exclude header
            
            error_log("[Certificate Processing] Total rows to process: $totalRows");
            
            // Create certificates directory if it doesn't exist
            $certDir = __DIR__ . '/../storage/certificates/' . $generationId . '/';
            if (!file_exists($certDir)) {
                mkdir($certDir, 0777, true);
            }

            $processedCount = 0;
            $positions = $generation['text_positions']; // Already decoded in getGeneration()

            // Process each row
            foreach ($worksheet->getRowIterator(2) as $row) {
                try {
                    $data = [];
                    foreach ($row->getCellIterator() as $cell) {
                        $data[] = $cell->getValue();
                    }
                    
                    error_log("[Certificate Processing] Processing row: " . implode(', ', $data));

                    // Generate certificate
                    $localPath = $this->generateCertificateLocally($data, $generation, $positions);
                    
                    if ($localPath) {
                        // Upload to S3
                        $s3Key = 'certificates/' . $generationId . '/' . basename($localPath);
                        $result = $this->s3->putObject([
                            'Bucket' => AWS_BUCKET_NAME,
                            'Key' => $s3Key,
                            'SourceFile' => $localPath,
                            'ACL' => 'private'
                        ]);

                        // Store certificate record
                        $this->storeCertificate($generationId, $data[0], $data[1], $data[2], [
                            'local_path' => $localPath,
                            's3_path' => $result['ObjectURL']
                        ]);

                        $processedCount++;
                        
                        // Update progress
                        $progress = ($processedCount / $totalRows) * 100;
                        $stmt = $conn->prepare("UPDATE certificate_generations SET progress = ?, generated_count = ? WHERE id = ?");
                        $stmt->execute([$progress, $processedCount, $generationId]);
                        
                        error_log("[Certificate Processing] Progress: $processedCount/$totalRows ($progress%)");
                    }
                } catch (\Exception $e) {
                    error_log("[Certificate Processing] Error processing row: " . $e->getMessage());
                    continue;
                }
            }

            // Mark as completed or failed
            $status = ($processedCount > 0) ? 'completed' : 'failed';
            $stmt = $conn->prepare("UPDATE certificate_generations SET status = ? WHERE id = ?");
            $stmt->execute([$status, $generationId]);
            
            error_log("[Certificate Processing] Generation $status. Total processed: $processedCount");
            
            // After successful generation and S3 upload, clean up local files
            $certDir = __DIR__ . '/../storage/certificates/' . $generationId . '/';
            $this->cleanupTempFiles($certDir);
            
            // Clean up the Excel file
            if (file_exists($file)) {
                unlink($file);
            }
            
            // Clean up template if it exists
            $templateDir = __DIR__ . '/../storage/templates/';
            if (file_exists($templateDir)) {
                $this->cleanupTempFiles($templateDir);
            }
            
            return true;

        } catch (\Exception $e) {
            error_log("[Certificate Processing] Error: " . $e->getMessage());
            if (isset($conn)) {
                $stmt = $conn->prepare("UPDATE certificate_generations SET status = 'failed' WHERE id = ?");
                $stmt->execute([$generationId]);
            }
            throw $e;
        }
    }

    private function generateCertificateLocally($data, $generation, $positions) {
        try {
            error_log("[Certificate Generation] Starting certificate generation for: " . implode(', ', $data));
            
            // Get template from S3
            $templateUrl = $generation['template_path'];
            $templateKey = ltrim(parse_url($templateUrl, PHP_URL_PATH), '/');
            
            // Setup directories
            $localTemplateDir = __DIR__ . '/../storage/templates/';
            $localCertDir = __DIR__ . '/../storage/certificates/' . $generation['id'] . '/';
            
            foreach ([$localTemplateDir, $localCertDir] as $dir) {
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
            }

            // Get template locally
            $localTemplatePath = $localTemplateDir . basename($templateKey);
            error_log("[Certificate Generation] Template path: $localTemplatePath");
            
            if (!file_exists($localTemplatePath)) {
                error_log("[Certificate Generation] Downloading template from S3");
                $this->s3->getObject([
                    'Bucket' => AWS_BUCKET_NAME,
                    'Key' => $templateKey,
                    'SaveAs' => $localTemplatePath
                ]);
            }

            // Generate the certificate using the preview function
            $outputFileName = uniqid('cert_') . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $data[0]) . '.jpg';
            $localOutputPath = $localCertDir . $outputFileName;
            
            // Add date to the data array for certificate generation
            $dataWithDate = $data;
            $dataWithDate[] = $generation['date_range']; // Add date_range as the fourth element
            
            // Use generatePreviewCertificate to create the certificate
            $generatedPath = $this->generatePreviewCertificate(
                $dataWithDate,
                $localTemplatePath,
                $positions,
                $localCertDir, // Use certificate directory instead of temp
                $generation['date_range'] // Pass date_range explicitly
            );

            // Move the generated file to its final location if needed
            if ($generatedPath !== $localOutputPath && file_exists($generatedPath)) {
                rename($generatedPath, $localOutputPath);
            }

            error_log("[Certificate Generation] Successfully generated certificate: $localOutputPath");
            return $localOutputPath;
            
        } catch (\Exception $e) {
            error_log("[Certificate Generation] Error: " . $e->getMessage());
            return false;
        }
    }

    private function updateGenerationProgress($generationId, $count, $progress) {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE certificate_generations SET generated_count = ?, progress = ? WHERE id = ?");
        $stmt->execute([$count, $progress, $generationId]);
        $_SESSION['generation_progress'] = $progress;
    }

    private function updateGenerationStatus($generationId, $status) {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE certificate_generations SET status = ? WHERE id = ?");
        $stmt->execute([$status, $generationId]);
    }

    private function getExcelRowCount($file) {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        return $worksheet->getHighestDataRow() - 1; // Exclude header row
    }

    public function download($generationId) {
        try {
            // Set execution time limit to 100 seconds
            set_time_limit(100);
            ini_set('max_execution_time', '100');
            
            // Also set memory limit higher if needed
            // ini_set('memory_limit', '256M');
            
            $conn = Database::getConnection();
            $stmt = $conn->prepare("SELECT * FROM certificates WHERE generation_id = ?");
            $stmt->execute([$generationId]);
            $certificates = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            if (empty($certificates)) {
                throw new \Exception('No certificates found');
            }
            
            // Create temp directory for this download
            $tempDir = __DIR__ . '/../storage/temp/download_' . uniqid();
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            
            $zipName = "certificates_{$generationId}.zip";
            $zipPath = $tempDir . '/' . $zipName;
            
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
                throw new \Exception('Cannot create zip file');
            }

            $successfulDownloads = 0;
            
            // Download and add files to zip
            foreach ($certificates as $cert) {
                try {
                    $s3Url = $cert['file_path'];
                    error_log("Processing file - Original URL: " . $s3Url);
                    
                    // Extract the path after amazonaws.com/
                    $pathParts = explode('amazonaws.com/', $s3Url);
                    if (count($pathParts) < 2) {
                        error_log("Invalid S3 URL format: " . $s3Url);
                        continue;
                    }
                    
                    $relativePath = $pathParts[1];
                    error_log("Extracted relative path: " . $relativePath);
                    
                    // Create temp file path maintaining folder structure
                    $tempFilePath = $tempDir . '/' . basename($relativePath);
                    
                    // Download file from URL
                    $fileContents = @file_get_contents($s3Url);
                    if ($fileContents === false) {
                        error_log("Failed to download file from URL: " . $s3Url);
                        continue;
                    }
                    
                    // Save to temp location
                    if (@file_put_contents($tempFilePath, $fileContents) === false) {
                        error_log("Failed to save file to temp location: " . $tempFilePath);
                        continue;
                    }
                    
                    // Add to zip if file exists
                    if (file_exists($tempFilePath)) {
                        // Use registration number and student name for the file name in zip
                        $zipFileName = sprintf(
                            "%s_%s.%s",
                            $cert['registration_number'],
                            preg_replace('/[^a-zA-Z0-9]/', '_', $cert['student_name']),
                            pathinfo($relativePath, PATHINFO_EXTENSION)
                        );
                        
                        if ($zip->addFile($tempFilePath, $zipFileName)) {
                            $successfulDownloads++;
                            error_log("Added to ZIP: " . $zipFileName);
                        } else {
                            error_log("Failed to add file to ZIP: " . $zipFileName);
                        }
                    }
                    
                } catch (\Exception $e) {
                    error_log("Error processing certificate: " . $e->getMessage());
                    continue;
                }
            }
            
            // Close the ZIP file
            $zip->close();
            
            // Check if we have any files in the ZIP
            if ($successfulDownloads > 0 && file_exists($zipPath)) {
                error_log("Serving ZIP file with {$successfulDownloads} certificates");
                
                // Clear any output that might have been sent
                if (ob_get_level()) ob_end_clean();
                
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename=' . $zipName);
                header('Content-Length: ' . filesize($zipPath));
                header('Pragma: no-cache');
                header('Expires: 0');
                
                readfile($zipPath);
                
                // Register cleanup function
                register_shutdown_function(function() use ($tempDir, $zipPath) {
                    if (file_exists($zipPath)) {
                        unlink($zipPath);
                    }
                    if (is_dir($tempDir)) {
                        $this->cleanupTempFiles($tempDir);
                    }
                });
                
                exit;
            } else {
                throw new \Exception("No certificates could be downloaded successfully");
            }
            
        } catch (\Exception $e) {
            error_log("Download error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to download certificates: ' . $e->getMessage();
            header('Location: /admin/certificate_generations');
            exit;
        }
    }

    private function cleanupTempFiles($dir) {
        try {
            if (!file_exists($dir)) {
                return;
            }
            
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($dir);
        } catch (\Exception $e) {
            error_log("Cleanup error: " . $e->getMessage());
        }
    }

    private function uploadToS3($filePath, $keyPrefix) {
        try {
            // Generate a unique file name
            $fileName = basename($filePath);
            $key = $keyPrefix . uniqid() . '_' . $fileName;

            // Upload the file to S3
            $result = $this->s3->putObject([
                'Bucket' => AWS_BUCKET_NAME,
                'Key'    => $key,
                'SourceFile' => $filePath,
                'ACL'    => 'public-read', // Set the ACL as needed
            ]);

            return $result['ObjectURL']; // Return the URL of the uploaded file
        } catch (Exception $e) {
            error_log("Error uploading to S3: " . $e->getMessage());
            return false; // Return false on failure
        }
    }

    private function getGeneration($generationId) {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT * FROM certificate_generations WHERE id = ?");
        $stmt->execute([$generationId]);
        $generation = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Decode the text_positions JSON
        if ($generation) {
            $generation['text_positions'] = json_decode($generation['text_positions'], true);
        }
        
        return $generation;
    }

    private function storeCertificate($generationId, $registrationNumber, $studentName, $grade, $paths) {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("INSERT INTO certificates (generation_id, registration_number, student_name, grade, file_path, local_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $generationId, 
            $registrationNumber, 
            $studentName, 
            $grade, 
            $paths['s3_path'],
            $paths['local_path']
        ]);
    }

    public function checkProgress($generationId) {
        try {
            // Clear any existing output buffers and ensure clean output
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Set JSON header early
            header('Content-Type: application/json');
            
            // Check if user is logged in
            if (!isset($_SESSION['admin'])) {
                echo json_encode([
                    'error' => 'Unauthorized',
                    'redirect' => '/'
                ]);
                exit;
            }
            
            $conn = Database::getConnection();
            $stmt = $conn->prepare("SELECT status, progress, generated_count, total_count FROM certificate_generations WHERE id = ?");
            $stmt->execute([$generationId]);
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$data) {
                echo json_encode([
                    'error' => 'Generation not found',
                    'status' => 'failed'
                ]);
                exit;
            }
            
            error_log("[Progress Check] Status for ID $generationId: " . json_encode($data));
            
            // Ensure all values are properly typed
            $response = [
                'status' => $data['status'],
                'progress' => (float)$data['progress'],
                'generated_count' => (int)$data['generated_count'],
                'total_count' => (int)$data['total_count']
            ];

            echo json_encode($response);
            
        } catch (\Exception $e) {
            error_log("[Progress Check] Error: " . $e->getMessage());
            echo json_encode([
                'error' => $e->getMessage(),
                'status' => 'failed'
            ]);
        }
        exit;
    }

    public function debug($generationId) {
        try {
            if (getenv('APP_ENV') !== 'production') {
                $generation = $this->getGeneration($generationId);
                header('Content-Type: application/json');
                echo json_encode([
                    'generation' => $generation,
                    'session' => [
                        'processing_id' => $_SESSION['processing_generation_id'] ?? null,
                        'progress' => $_SESSION['generation_progress'] ?? null
                    ],
                    'memory_usage' => memory_get_usage(true),
                    'peak_memory' => memory_get_peak_usage(true),
                    'execution_time' => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
                ]);
            }
        } catch (\Exception $e) {
            error_log("[Debug] Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function preview() {
        try {
            error_log("[Certificate Preview] Starting preview generation");
            
            // Validate files
            if (!isset($_FILES['template']) || !isset($_FILES['data_file'])) {
                throw new \Exception('Please upload both template and data file');
            }

            // Create temp directory if it doesn't exist
            $tempDir = __DIR__ . '/../storage/temp';
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            // Handle template file
            $tempTemplatePath = $tempDir . '/template_' . uniqid() . '.jpg';
            if (!move_uploaded_file($_FILES['template']['tmp_name'], $tempTemplatePath)) {
                throw new \Exception('Failed to upload template');
            }

            // Handle Excel file
            $tempExcelPath = $tempDir . '/data_' . uniqid() . '.xlsx';
            if (!move_uploaded_file($_FILES['data_file']['tmp_name'], $tempExcelPath)) {
                throw new \Exception('Failed to upload data file');
            }

            // Load Excel file
            $spreadsheet = IOFactory::load($tempExcelPath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get first row data
            $firstRow = [];
            foreach ($worksheet->getRowIterator(2, 2) as $row) { // Start from row 2
                foreach ($row->getCellIterator() as $cell) {
                    $firstRow[] = $cell->getValue();
                }
                break;
            }

            if (empty($firstRow)) {
                throw new \Exception("No data found in Excel file");
            }

            // Get and validate positions data
            $positions = isset($_POST['positions']) ? $_POST['positions'] : null;
            error_log("[Certificate Preview] Raw positions data: " . print_r($positions, true));

            // If positions is a string (JSON), decode it
            if (is_string($positions)) {
                $positions = json_decode($positions, true);
            }

            // Validate positions structure
            if (!is_array($positions)) {
                error_log("[Certificate Preview] Invalid positions format: " . gettype($positions));
                throw new \Exception("Invalid positions format. Expected JSON object.");
            }

            // Ensure all required fields exist with coordinates
            $requiredFields = ['registration_number', 'name', 'grade'];
            foreach ($requiredFields as $field) {
                if (!isset($positions[$field]) || 
                    !isset($positions[$field]['x']) || 
                    !isset($positions[$field]['y'])) {
                    error_log("[Certificate Preview] Missing coordinates for field: " . $field);
                    error_log("[Certificate Preview] Available positions: " . json_encode($positions));
                    throw new \Exception("Missing coordinates for {$field}. Please set all text positions.");
                }
            }

            // Normalize coordinates to numbers
            foreach ($positions as $field => &$pos) {
                $pos['x'] = (float)$pos['x'];
                $pos['y'] = (float)$pos['y'];
            }

            error_log("[Certificate Preview] Normalized positions: " . json_encode($positions));

            // Generate preview with validated positions
            $previewPath = $this->generatePreviewCertificate($firstRow, $tempTemplatePath, $positions, $tempDir);

            // Clean up temp files
            unlink($tempExcelPath);
            unlink($tempTemplatePath);

            // Return preview path and the exact positions used
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'previewUrl' => '/storage/temp/' . basename($previewPath),
                'data' => [
                    'registration_number' => $firstRow[0] ?? '',
                    'name' => $firstRow[1] ?? '',
                    'grade' => $firstRow[2] ?? '',
                    'date' => $_POST['date_range'] ?? ''
                ],
                'positions' => $positions // Return the exact positions used
            ]);

        } catch (\Exception $e) {
            error_log("[Certificate Preview] Error: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'debug' => [
                    'received_positions' => $_POST['positions'] ?? 'not set',
                    'decoded_positions' => $positions ?? null
                ]
            ]);
        }
    }

    private function generatePreviewCertificate($data, $templatePath, $positions, $tempDir, $dateRange = null) {
        try {
            error_log("[Preview Generation] Starting with positions: " . json_encode($positions));
            
            // Get image dimensions
            $imageInfo = getimagesize($templatePath);
            if (!$imageInfo) {
                throw new \Exception("Invalid template image");
            }
            
            $imageWidth = $imageInfo[0];
            $imageHeight = $imageInfo[1];
            
            // Create image based on type
            switch ($imageInfo['mime']) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($templatePath);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($templatePath);
                    break;
                default:
                    throw new \Exception('Unsupported image type: ' . $imageInfo['mime']);
            }

            if (!$image) {
                throw new \Exception("Failed to create image resource");
            }

            // Configure image
            imagesavealpha($image, true);
            imagealphablending($image, true);

            // Create text color (black)
            $textColor = imagecolorallocate($image, 0, 0, 0);
            
            // Use font
            $fontPath = __DIR__ . '/../assets/fonts/Poppins-Bold.ttf';
            if (!file_exists($fontPath)) {
                throw new \Exception("Font file not found: $fontPath");
            }

            // Add text to image using exact pixel coordinates
            foreach ($positions as $field => $pos) {
                $text = '';
                $fontSize = 0;
                
                switch ($field) {
                    case 'registration_number':
                        $text = $data[0] ?? '';
                        $fontSize = round($imageHeight * 0.03);
                        break;
                    case 'name':
                        $text = $data[1] ?? '';
                        $fontSize = round($imageHeight * 0.035);
                        break;
                    case 'grade':
                        $text = $data[2] ?? '';
                        $fontSize = round($imageHeight * 0.03);
                        break;
                    case 'date':
                        // Use either passed date_range or POST data
                        $text = $dateRange ?? $_POST['date_range'] ?? '';
                        $fontSize = round($imageHeight * 0.015);
                        break;
                    default:
                        continue 2;
                }

                if (empty($text)) continue;

                // Get text dimensions
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
                if ($bbox === false) {
                    throw new \Exception("Failed to calculate text dimensions");
                }

                // Calculate text dimensions and position
                $textWidth = $bbox[2] - $bbox[0];
                $textHeight = $bbox[1] - $bbox[7];
                $baselineOffset = abs($bbox[7]);

                // Use exact pixel coordinates from positions
                $x = round($pos['x'] - ($textWidth / 2));
                $y = round($pos['y'] + $baselineOffset);

                error_log("[Preview Generation] Placing '$text' at x=$x, y=$y (original pos: x={$pos['x']}, y={$pos['y']})");

                // Add text to image
                $result = imagettftext(
                    $image,
                    $fontSize,
                    0,
                    $x,
                    $y,
                    $textColor,
                    $fontPath,
                    $text
                );

                if ($result === false) {
                    throw new \Exception("Failed to add text: $text");
                }
            }

            // Save preview
            $previewPath = $tempDir . '/preview_' . uniqid() . '.jpg';
            imagejpeg($image, $previewPath, 90);
            imagedestroy($image);

            return $previewPath;

        } catch (\Exception $e) {
            error_log("[Preview Generation] Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function downloadGeneration($id) {
        try {
            // Set execution time limit to 100 seconds
            set_time_limit(100);
            ini_set('max_execution_time', '100');
            
            // Also set memory limit higher if needed
            // ini_set('memory_limit', '256M');
            
            $conn = Database::getConnection();
            $stmt = $conn->prepare("SELECT * FROM certificate_generations WHERE id = ?");
            $stmt->execute([$id]);
            $generation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$generation) {
                throw new \Exception("Certificate generation not found");
            }

            // Check if local directory exists, if not create it
            $localDir = "storage/certificates/{$id}";
            if (!file_exists($localDir)) {
                mkdir($localDir, 0777, true);
            }

            // Path for the final zip file
            $zipPath = "storage/certificates/generation_{$id}.zip";
            
            // If zip doesn't exist locally, create it
            if (!file_exists($zipPath)) {
                // Create ZIP archive
                $zip = new \ZipArchive();
                if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
                    throw new \Exception("Cannot create zip file");
                }

                // Get certificates for this generation
                $stmt = $conn->prepare("SELECT * FROM certificates WHERE generation_id = ?");
                $stmt->execute([$id]);
                $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($certificates as $cert) {
                    try {
                        // Extract just the path part after 'certificates/'
                        $s3Key = '';
                        if (strpos($cert['file_path'], 'https://') !== false) {
                            // If it's a full URL, extract just the path part
                            $pathParts = parse_url($cert['file_path']);
                            $s3Key = ltrim($pathParts['path'], '/');
                        } else {
                            // If it's already a path, use it directly
                            $s3Key = ltrim($cert['file_path'], '/');
                        }

                        $localFilePath = "{$localDir}/" . basename($s3Key);

                        // Download from S3 if file doesn't exist locally
                        if (!file_exists($localFilePath)) {
                            error_log("Downloading certificate from S3: " . $s3Key);
                            $result = $this->s3->getObject([
                                'Bucket' => AWS_BUCKET_NAME,
                                'Key'    => $s3Key,
                                'SaveAs' => $localFilePath
                            ]);
                        }

                        // Add to ZIP
                        $zip->addFile($localFilePath, basename($localFilePath));
                    } catch (\Exception $e) {
                        error_log("Error downloading certificate {$cert['file_path']}: " . $e->getMessage());
                        continue; // Skip this file and continue with others
                    }
                }

                $zip->close();
            }

            // Serve the ZIP file
            if (file_exists($zipPath)) {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="certificates_' . $id . '.zip"');
                header('Content-Length: ' . filesize($zipPath));
                header('Pragma: no-cache');
                header('Expires: 0');
                
                readfile($zipPath);
                
                // Clean up after sending
                register_shutdown_function(function() use ($zipPath, $localDir) {
                    if (file_exists($zipPath)) {
                        unlink($zipPath);
                    }
                    $this->cleanupTempFiles($localDir);
                });
                exit;
            } else {
                throw new \Exception("ZIP file could not be created");
            }

        } catch (\Exception $e) {
            error_log("Error downloading certificates: " . $e->getMessage());
            $_SESSION['error'] = "Error downloading certificates: " . $e->getMessage();
            header('Location: /admin/certificate_generations');
            exit;
        }
    }

    // Helper function to clean up old files
    private function cleanupOldFiles($directory, $maxAge = 3600) {
        if (is_dir($directory)) {
            foreach (new \DirectoryIterator($directory) as $file) {
                if (!$file->isDot() && $file->isFile()) {
                    if (time() - $file->getCTime() >= $maxAge) {
                        unlink($file->getPathname());
                    }
                }
            }
        }
    }
} 