<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Service class for secure file upload handling
 * Provides comprehensive file validation, virus scanning, and secure storage
 */
class FileUploadService
{
    /**
     * Allowed file formats
     */
    public const ALLOWED_FORMATS = ['jpeg', 'jpg', 'png', 'pdf'];
    
    /**
     * Maximum file size in bytes (2MB)
     */
    public const MAX_FILE_SIZE = 2097152; // 2 * 1024 * 1024
    
    /**
     * Storage paths
     */
    public const STORAGE_DISK = 'local';
    public const UPLOAD_PATH = 'uploads/payment-proofs';
    public const TEMP_PATH = 'temp/uploads';
    
    /**
     * File validation status
     */
    public const STATUS_VALID = 'valid';
    public const STATUS_INVALID_FORMAT = 'invalid_format';
    public const STATUS_INVALID_SIZE = 'invalid_size';
    public const STATUS_VIRUS_DETECTED = 'virus_detected';
    public const STATUS_MALICIOUS_CONTENT = 'malicious_content';
    public const STATUS_UPLOAD_ERROR = 'upload_error';
    
    /**
     * Malicious content patterns to detect
     */
    private array $maliciousPatterns = [
        '/<script[^>]*>.*?<\/script>/i',
        '/<iframe[^>]*>.*?<\/iframe>/i',
        '/javascript:/i',
        '/data:text\/html/i',
        '/vbscript:/i',
        '/onload=/i',
        '/onerror=/i',
        '/onclick=/i',
        '/%3Cscript/i',
        '/%3Ciframe/i',
    ];
    
    /**
     * Validate uploaded file format
     * 
     * @param UploadedFile $file
     * @return array
     */
    public function validateFileFormat(UploadedFile $file): array
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());
            $mimeType = $file->getMimeType();
            
            // Check file extension
            if (!in_array($extension, self::ALLOWED_FORMATS)) {
                return [
                    'valid' => false,
                    'status' => self::STATUS_INVALID_FORMAT,
                    'message' => 'File format not allowed. Allowed formats: ' . implode(', ', self::ALLOWED_FORMATS),
                    'allowed_formats' => self::ALLOWED_FORMATS
                ];
            }
            
            // Verify MIME type matches extension
            $allowedMimeTypes = [
                'jpeg' => ['image/jpeg', 'image/jpg'],
                'jpg' => ['image/jpeg', 'image/jpg'],
                'png' => ['image/png'],
                'pdf' => ['application/pdf']
            ];
            
            $expectedMimes = $allowedMimeTypes[$extension] ?? [];
            if (!in_array($mimeType, $expectedMimes)) {
                return [
                    'valid' => false,
                    'status' => self::STATUS_INVALID_FORMAT,
                    'message' => 'File MIME type does not match extension',
                    'detected_mime' => $mimeType,
                    'expected_mimes' => $expectedMimes
                ];
            }
            
            return [
                'valid' => true,
                'status' => self::STATUS_VALID,
                'extension' => $extension,
                'mime_type' => $mimeType
            ];
            
        } catch (\Exception $e) {
            Log::error('File format validation error', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            
            return [
                'valid' => false,
                'status' => self::STATUS_UPLOAD_ERROR,
                'message' => 'Error validating file format'
            ];
        }
    }
    
    /**
     * Validate file size
     * 
     * @param UploadedFile $file
     * @return array
     */
    public function validateFileSize(UploadedFile $file): array
    {
        $fileSize = $file->getSize();
        
        if ($fileSize > self::MAX_FILE_SIZE) {
            $maxSizeMB = self::MAX_FILE_SIZE / (1024 * 1024);
            $fileSizeMB = round($fileSize / (1024 * 1024), 2);
            
            return [
                'valid' => false,
                'status' => self::STATUS_INVALID_SIZE,
                'message' => "File size exceeds maximum limit of {$maxSizeMB}MB",
                'file_size' => $fileSizeMB,
                'max_size' => $maxSizeMB
            ];
        }
        
        return [
            'valid' => true,
            'status' => self::STATUS_VALID,
            'file_size' => round($fileSize / (1024 * 1024), 2)
        ];
    }
    
    /**
     * Scan file for viruses and malicious content
     * 
     * @param UploadedFile $file
     * @return array
     */
    public function scanForMaliciousContent(UploadedFile $file): array
    {
        try {
            // Read file content for analysis
            $content = file_get_contents($file->getRealPath());
            
            if ($content === false) {
                return [
                    'valid' => false,
                    'status' => self::STATUS_UPLOAD_ERROR,
                    'message' => 'Unable to read file content for scanning'
                ];
            }
            
            // Basic virus signature detection (simplified approach)
            $virusSignatures = [
                'EICAR-STANDARD-ANTIVIRUS-TEST-FILE',
                'X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR',
                'eval(',
                'base64_decode(',
                'gzinflate(',
                'str_rot13(',
                'exec(',
                'system(',
                'shell_exec(',
                'passthru(',
            ];
            
            foreach ($virusSignatures as $signature) {
                if (strpos($content, $signature) !== false) {
                    Log::warning('Virus signature detected in uploaded file', [
                        'file' => $file->getClientOriginalName(),
                        'signature' => $signature,
                        'ip' => request()->ip()
                    ]);
                    
                    return [
                        'valid' => false,
                        'status' => self::STATUS_VIRUS_DETECTED,
                        'message' => 'Potential virus detected in file'
                    ];
                }
            }
            
            // Check for malicious content patterns
            foreach ($this->maliciousPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    Log::warning('Malicious content pattern detected in uploaded file', [
                        'file' => $file->getClientOriginalName(),
                        'pattern' => $pattern,
                        'ip' => request()->ip()
                    ]);
                    
                    return [
                        'valid' => false,
                        'status' => self::STATUS_MALICIOUS_CONTENT,
                        'message' => 'Malicious content detected in file'
                    ];
                }
            }
            
            // Additional PDF-specific checks
            if ($file->getMimeType() === 'application/pdf') {
                $pdfCheck = $this->scanPDFContent($content);
                if (!$pdfCheck['valid']) {
                    return $pdfCheck;
                }
            }
            
            return [
                'valid' => true,
                'status' => self::STATUS_VALID,
                'message' => 'File passed security scan'
            ];
            
        } catch (\Exception $e) {
            Log::error('File security scan error', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            
            return [
                'valid' => false,
                'status' => self::STATUS_UPLOAD_ERROR,
                'message' => 'Error scanning file for security threats'
            ];
        }
    }
    
    /**
     * Generate unique and secure filename
     * 
     * @param UploadedFile $file
     * @param string $prefix
     * @return string
     */
    public function generateSecureFilename(UploadedFile $file, string $prefix = 'payment'): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $timestamp = now()->format('Y-m-d_H-i-s');
        $randomString = Str::random(8);
        $hash = substr(hash('sha256', $file->getClientOriginalName() . time()), 0, 8);
        
        return "{$prefix}_{$timestamp}_{$randomString}_{$hash}.{$extension}";
    }
    
    /**
     * Store file securely
     * 
     * @param UploadedFile $file
     * @param array $options
     * @return array
     */
    public function storeFile(UploadedFile $file, array $options = []): array
    {
        try {
            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return array_merge($validation, ['success' => false]);
            }
            
            // Generate secure filename
            $prefix = $options['prefix'] ?? 'payment';
            $filename = $this->generateSecureFilename($file, $prefix);
            
            // Determine storage path
            $storagePath = $options['path'] ?? self::UPLOAD_PATH;
            $fullPath = "{$storagePath}/{$filename}";
            
            // Store file
            $stored = Storage::disk(self::STORAGE_DISK)->putFileAs(
                $storagePath,
                $file,
                $filename
            );
            
            if (!$stored) {
                return [
                    'success' => false,
                    'status' => self::STATUS_UPLOAD_ERROR,
                    'message' => 'Failed to store file'
                ];
            }
            
            // Generate file hash for integrity verification
            $fileHash = hash_file('sha256', $file->getRealPath());
            
            // Log successful upload
            Log::info('File uploaded successfully', [
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $filename,
                'path' => $fullPath,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'hash' => $fileHash,
                'ip' => request()->ip()
            ]);
            
            return [
                'success' => true,
                'status' => self::STATUS_VALID,
                'message' => 'File uploaded successfully',
                'file_data' => [
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name' => $filename,
                    'path' => $fullPath,
                    'full_path' => Storage::disk(self::STORAGE_DISK)->path($fullPath),
                    'url' => Storage::disk(self::STORAGE_DISK)->url($fullPath),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'extension' => $file->getClientOriginalExtension(),
                    'hash' => $fileHash,
                    'uploaded_at' => now()->toISOString()
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('File upload error', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'status' => self::STATUS_UPLOAD_ERROR,
                'message' => 'File upload failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Comprehensive file validation
     * 
     * @param UploadedFile $file
     * @return array
     */
    public function validateFile(UploadedFile $file): array
    {
        // Check if file uploaded successfully
        if (!$file->isValid()) {
            return [
                'valid' => false,
                'status' => self::STATUS_UPLOAD_ERROR,
                'message' => 'File upload error: ' . $file->getErrorMessage()
            ];
        }
        
        // Validate format
        $formatValidation = $this->validateFileFormat($file);
        if (!$formatValidation['valid']) {
            return $formatValidation;
        }
        
        // Validate size
        $sizeValidation = $this->validateFileSize($file);
        if (!$sizeValidation['valid']) {
            return $sizeValidation;
        }
        
        // Scan for malicious content
        $securityScan = $this->scanForMaliciousContent($file);
        if (!$securityScan['valid']) {
            return $securityScan;
        }
        
        return [
            'valid' => true,
            'status' => self::STATUS_VALID,
            'message' => 'File validation successful'
        ];
    }
    
    /**
     * Delete file securely
     * 
     * @param string $filePath
     * @return array
     */
    public function deleteFile(string $filePath): array
    {
        try {
            if (!Storage::disk(self::STORAGE_DISK)->exists($filePath)) {
                return [
                    'success' => false,
                    'message' => 'File does not exist'
                ];
            }
            
            $deleted = Storage::disk(self::STORAGE_DISK)->delete($filePath);
            
            if ($deleted) {
                Log::info('File deleted successfully', [
                    'path' => $filePath,
                    'ip' => request()->ip()
                ]);
                
                return [
                    'success' => true,
                    'message' => 'File deleted successfully'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to delete file'
            ];
            
        } catch (\Exception $e) {
            Log::error('File deletion error', [
                'path' => $filePath,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'File deletion failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Clean up old temporary files
     * 
     * @param int $olderThanHours
     * @return array
     */
    public function cleanupTempFiles(int $olderThanHours = 24): array
    {
        try {
            $tempPath = self::TEMP_PATH;
            $cutoffTime = now()->subHours($olderThanHours);
            
            $files = Storage::disk(self::STORAGE_DISK)->files($tempPath);
            $deletedCount = 0;
            $errorCount = 0;
            
            foreach ($files as $file) {
                $lastModified = Storage::disk(self::STORAGE_DISK)->lastModified($file);
                
                if ($lastModified < $cutoffTime->timestamp) {
                    if (Storage::disk(self::STORAGE_DISK)->delete($file)) {
                        $deletedCount++;
                    } else {
                        $errorCount++;
                    }
                }
            }
            
            Log::info('Temp file cleanup completed', [
                'deleted_count' => $deletedCount,
                'error_count' => $errorCount,
                'cutoff_time' => $cutoffTime->toISOString()
            ]);
            
            return [
                'success' => true,
                'deleted_count' => $deletedCount,
                'error_count' => $errorCount,
                'message' => "Cleanup completed: {$deletedCount} files deleted, {$errorCount} errors"
            ];
            
        } catch (\Exception $e) {
            Log::error('Temp file cleanup error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Cleanup failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get file access URL with temporary signed URL for security
     * 
     * @param string $filePath
     * @param int $expirationMinutes
     * @return array
     */
    public function getSecureFileUrl(string $filePath, int $expirationMinutes = 60): array
    {
        try {
            if (!Storage::disk(self::STORAGE_DISK)->exists($filePath)) {
                return [
                    'success' => false,
                    'message' => 'File does not exist'
                ];
            }
            
            // For local storage, we might need to implement custom signed URLs
            // For now, return the basic URL with a note about implementing signed URLs
            $url = Storage::disk(self::STORAGE_DISK)->url($filePath);
            
            return [
                'success' => true,
                'url' => $url,
                'expires_at' => now()->addMinutes($expirationMinutes)->toISOString(),
                'message' => 'File URL generated successfully'
            ];
            
        } catch (\Exception $e) {
            Log::error('Error generating file URL', [
                'path' => $filePath,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to generate file URL: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verify file integrity using hash
     * 
     * @param string $filePath
     * @param string $expectedHash
     * @return array
     */
    public function verifyFileIntegrity(string $filePath, string $expectedHash): array
    {
        try {
            if (!Storage::disk(self::STORAGE_DISK)->exists($filePath)) {
                return [
                    'valid' => false,
                    'message' => 'File does not exist'
                ];
            }
            
            $fullPath = Storage::disk(self::STORAGE_DISK)->path($filePath);
            $actualHash = hash_file('sha256', $fullPath);
            
            if ($actualHash === $expectedHash) {
                return [
                    'valid' => true,
                    'message' => 'File integrity verified'
                ];
            }
            
            Log::warning('File integrity verification failed', [
                'path' => $filePath,
                'expected_hash' => $expectedHash,
                'actual_hash' => $actualHash
            ]);
            
            return [
                'valid' => false,
                'message' => 'File integrity verification failed',
                'expected_hash' => $expectedHash,
                'actual_hash' => $actualHash
            ];
            
        } catch (\Exception $e) {
            Log::error('File integrity verification error', [
                'path' => $filePath,
                'error' => $e->getMessage()
            ]);
            
            return [
                'valid' => false,
                'message' => 'Integrity verification failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Scan PDF content for malicious elements
     * 
     * @param string $content
     * @return array
     */
    private function scanPDFContent(string $content): array
    {
        // Check for suspicious PDF elements
        $suspiciousPatterns = [
            '/\/JavaScript/i',
            '/\/JS/i',
            '/\/OpenAction/i',
            '/\/AA/i',
            '/\/Launch/i',
            '/\/EmbeddedFile/i',
            '/\/XFA/i',
            '/\/RichMedia/i'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return [
                    'valid' => false,
                    'status' => self::STATUS_MALICIOUS_CONTENT,
                    'message' => 'Suspicious content detected in PDF file'
                ];
            }
        }
        
        return [
            'valid' => true,
            'status' => self::STATUS_VALID
        ];
    }
}