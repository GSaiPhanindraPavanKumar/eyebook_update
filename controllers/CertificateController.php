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
    }

    public function index() {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT * FROM certificate_generations ORDER BY created_at DESC");
        $stmt->execute();
        $generations = $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
            
            // Log positions data
            error_log("[Certificate Generation] Positions data: " . $_POST['positions']);
            $positions = json_decode($_POST['positions'], true);
            if ($positions === null) {
                error_log("[Certificate Generation] Failed to decode positions JSON");
                throw new \Exception("Invalid positions data");
            }
            error_log("[Certificate Generation] Decoded positions: " . print_r($positions, true));
            
            // Insert generation record with excel_file_path
            $stmt = $conn->prepare("INSERT INTO certificate_generations (subject, template_path, template_type, text_positions, status, generated_count, total_count, excel_file_path) VALUES (?, ?, ?, ?, 'processing', 0, 0, ?)");
            $stmt->execute([
                $_POST['subject'],
                $templateUrl,
                $_FILES['template']['type'],
                $_POST['positions'],
                $excelTempPath
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
        
        $requiredHeaders = ['Registration Number', 'Name', 'Grade in %'];
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

            // Create image
            $imageInfo = getimagesize($localTemplatePath);
            if (!$imageInfo) {
                throw new \Exception("Failed to get image info");
            }
            
            error_log("[Certificate Generation] Image dimensions: {$imageInfo[0]}x{$imageInfo[1]}");
            
            $image = null;
            switch ($imageInfo['mime']) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($localTemplatePath);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($localTemplatePath);
                    break;
                default:
                    throw new \Exception('Unsupported image type: ' . $imageInfo['mime']);
            }

            if (!$image) {
                throw new \Exception("Failed to create image resource");
            }

            // Get image dimensions
            $imageWidth = $imageInfo[0];
            $imageHeight = $imageInfo[1];

            // Configure image
            imagesavealpha($image, true);
            imagealphablending($image, true);

            // Create text color (black)
            $textColor = imagecolorallocate($image, 0, 0, 0);
            
            // Use Poppins-Bold font
            $fontPath = __DIR__ . '/../assets/fonts/Poppins-Bold.ttf';
            if (!file_exists($fontPath)) {
                throw new \Exception("Font file not found: $fontPath");
            }

            // Standard preview width used in frontend
            $PREVIEW_WIDTH = 1000;
            
            // Calculate scaling factors based on actual image dimensions
            $scaleX = $imageWidth / $PREVIEW_WIDTH;
            $scaleY = $imageHeight / $PREVIEW_WIDTH;

            // Add text to image
            foreach ($positions as $field => $pos) {
                $text = '';
                switch ($field) {
                    case 'registration_number': 
                        $text = $data[0]; 
                        $fontSize = round(24 * min($scaleX, $scaleY)); // Scale font proportionally
                        break;
                    case 'name': 
                        $text = $data[1]; 
                        $fontSize = round(32 * min($scaleX, $scaleY)); // Scale font proportionally
                        break;
                    case 'grade': 
                        $text = $data[2]; 
                        $fontSize = round(24 * min($scaleX, $scaleY)); // Scale font proportionally
                        break;
                    default: 
                        continue 2;
                }

                // Calculate actual coordinates based on image dimensions
                // Scale both X and Y coordinates using their respective scaling factors
                $x = round($pos['x'] * $scaleX);
                $y = round($pos['y'] * $scaleY);

                // Calculate text dimensions for precise positioning
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
                if ($bbox === false) {
                    throw new \Exception("Failed to calculate text dimensions for: $text");
                }

                // Adjust Y position to account for text baseline
                $y = $y + abs($bbox[7]); 

                error_log("[Certificate Generation] Text: '$text'");
                error_log("[Certificate Generation] Original position: x={$pos['x']}, y={$pos['y']}");
                error_log("[Certificate Generation] Scaled position: x=$x, y=$y");
                error_log("[Certificate Generation] Font size: $fontSize");
                error_log("[Certificate Generation] Scale factors: scaleX=$scaleX, scaleY=$scaleY");

                // Add text with error checking
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
                    error_log("[Certificate Generation] Failed to add text: $text");
                    throw new \Exception("Failed to add text: $text");
                }
            }

            // Save certificate
            $outputFileName = uniqid('cert_') . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $data[0]) . '.jpg';
            $localOutputPath = $localCertDir . $outputFileName;
            
            error_log("[Certificate Generation] Saving certificate to: $localOutputPath");
            
            if (!imagejpeg($image, $localOutputPath, 100)) {
                throw new \Exception("Failed to save certificate image");
            }

            imagedestroy($image);
            error_log("[Certificate Generation] Successfully generated certificate: $localOutputPath");
            
            return $localOutputPath;
            
        } catch (\Exception $e) {
            error_log("[Certificate Generation] Error: " . $e->getMessage());
            if (isset($image) && is_resource($image)) {
                imagedestroy($image);
            }
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
            
            // Download and add files to zip
            foreach ($certificates as $cert) {
                try {
                    // Get file key from S3 path
                    $s3Key = str_replace('https://' . AWS_BUCKET_NAME . '.s3.' . AWS_REGION . '.amazonaws.com/', '', $cert['file_path']);
                    
                    // Download file from S3 to temp location
                    $tempFilePath = $tempDir . '/' . basename($cert['file_path']);
                    $this->s3->getObject([
                        'Bucket' => AWS_BUCKET_NAME,
                        'Key' => $s3Key,
                        'SaveAs' => $tempFilePath
                    ]);
                    
                    // Add to zip with proper name
                    $zip->addFile(
                        $tempFilePath,
                        "{$cert['registration_number']}_{$cert['student_name']}.jpg"
                    );
                    
                } catch (\Exception $e) {
                    error_log("Error downloading certificate {$cert['file_path']}: " . $e->getMessage());
                    continue;
                }
            }
            
            $zip->close();
            
            // Send zip file
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename=' . $zipName);
            header('Content-Length: ' . filesize($zipPath));
            readfile($zipPath);
            
            // Cleanup temp directory and files
            $this->cleanupTempFiles($tempDir);
            
        } catch (\Exception $e) {
            error_log("Download error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to download certificates';
            header('Location: /admin/certificate_generations');
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
            // Clear any existing output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            header('Content-Type: application/json');
            
            $conn = Database::getConnection();
            $stmt = $conn->prepare("SELECT status, progress, generated_count, total_count FROM certificate_generations WHERE id = ?");
            $stmt->execute([$generationId]);
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$data) {
                throw new \Exception("Generation not found");
            }
            
            error_log("[Progress Check] Status for ID $generationId: " . json_encode($data));
            
            echo json_encode([
                'status' => $data['status'],
                'progress' => (float)$data['progress'],
                'generated_count' => (int)$data['generated_count'],
                'total_count' => (int)$data['total_count']
            ]);
            
        } catch (\Exception $e) {
            error_log("[Progress Check] Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
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
                throw new \Exception('Missing required files');
            }

            // Create temp directory if it doesn't exist
            $tempDir = __DIR__ . '/../storage/temp/';
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            // Save template temporarily
            $tempTemplatePath = $tempDir . uniqid() . '_' . $_FILES['template']['name'];
            if (!move_uploaded_file($_FILES['template']['tmp_name'], $tempTemplatePath)) {
                throw new \Exception("Failed to save template file");
            }

            // Load first row from Excel
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES['data_file']['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $firstRow = [];
            foreach ($worksheet->getRowIterator(2, 2) as $row) { // Get first data row
                foreach ($row->getCellIterator() as $cell) {
                    $firstRow[] = $cell->getValue();
                }
                break;
            }

            if (empty($firstRow)) {
                throw new \Exception("No data found in Excel file");
            }

            // Generate preview certificate
            $positions = json_decode($_POST['positions'], true);
            $previewPath = $this->generatePreviewCertificate($firstRow, $tempTemplatePath, $positions);

            // Return preview path and data
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'previewUrl' => '/storage/temp/' . basename($previewPath),
                'data' => [
                    'registration_number' => $firstRow[0],
                    'name' => $firstRow[1],
                    'grade' => $firstRow[2]
                ]
            ]);

        } catch (\Exception $e) {
            error_log("[Certificate Preview] Error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function generatePreviewCertificate($data, $templatePath, $positions) {
        try {
            // Create image from template
            $imageInfo = getimagesize($templatePath);
            $image = null;
            
            // Get original image dimensions
            $imageWidth = $imageInfo[0];
            $imageHeight = $imageInfo[1];
            
            // Standard preview width used in frontend
            $PREVIEW_WIDTH = 1000;
            
            // Calculate scaling factors
            $scaleX = $imageWidth / $PREVIEW_WIDTH;
            $scaleY = $imageHeight / $PREVIEW_WIDTH;

            switch ($imageInfo['mime']) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($templatePath);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($templatePath);
                    break;
                default:
                    throw new \Exception('Unsupported image type');
            }

            if (!$image) {
                throw new \Exception("Failed to create image resource");
            }

            // Configure image
            imagesavealpha($image, true);
            imagealphablending($image, true);

            // Add text
            $textColor = imagecolorallocate($image, 0, 0, 0);
            $fontPath = __DIR__ . '/../assets/fonts/Poppins-Bold.ttf';

            foreach ($positions as $field => $pos) {
                $text = '';
                switch ($field) {
                    case 'registration_number': 
                        $text = $data[0]; 
                        $fontSize = round(24 * min($scaleX, $scaleY));
                        break;
                    case 'name': 
                        $text = $data[1]; 
                        $fontSize = round(32 * min($scaleX, $scaleY));
                        break;
                    case 'grade': 
                        $text = $data[2]; 
                        $fontSize = round(24 * min($scaleX, $scaleY));
                        break;
                    default: 
                        continue 2;
                }

                // Calculate actual coordinates using both scaling factors
                $x = round($pos['x'] * $scaleX);
                $y = round($pos['y'] * $scaleY);

                // Calculate text dimensions
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
                
                // Adjust Y position to account for text baseline
                $y = $y + abs($bbox[7]);

                imagettftext(
                    $image,
                    $fontSize,
                    0,
                    $x,
                    $y,
                    $textColor,
                    $fontPath,
                    $text
                );
            }

            // Save preview
            $previewPath = __DIR__ . '/../storage/temp/preview_' . uniqid() . '.jpg';
            imagejpeg($image, $previewPath, 90);
            imagedestroy($image);

            // Clean up after successful preview generation
            if (file_exists($previewPath)) {
                register_shutdown_function(function() use ($previewPath) {
                    if (file_exists($previewPath)) {
                        unlink($previewPath);
                    }
                });
            }

            return $previewPath;
        } catch (\Exception $e) {
            error_log("[Preview Generation] Error: " . $e->getMessage());
            throw $e;
        }
    }
} 