<?php

namespace Tests\Feature;

use App\Services\FileUploadService;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FileUploadServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_file_upload_service_can_be_resolved_from_container()
    {
        // Act
        $service = app(FileUploadService::class);
        
        // Assert
        $this->assertInstanceOf(FileUploadService::class, $service);
    }

    public function test_file_upload_service_is_singleton()
    {
        // Act
        $service1 = app(FileUploadService::class);
        $service2 = app(FileUploadService::class);
        
        // Assert
        $this->assertSame($service1, $service2);
    }

    public function test_complete_file_upload_workflow()
    {
        // Arrange
        $service = app(FileUploadService::class);
        $file = UploadedFile::fake()->image('payment_proof.jpg', 800, 600)->size(1024);

        // Act - Upload file
        $uploadResult = $service->storeFile($file, [
            'prefix' => 'integration_test',
            'path' => 'test-uploads/payment-proofs'
        ]);

        // Assert - Upload success
        $this->assertTrue($uploadResult['success']);
        $this->assertEquals(FileUploadService::STATUS_VALID, $uploadResult['status']);
        
        $fileData = $uploadResult['file_data'];
        $this->assertStringStartsWith('integration_test_', $fileData['stored_name']);
        $this->assertTrue(Storage::disk('local')->exists($fileData['path']));

        // Act - Verify file integrity
        $integrityResult = $service->verifyFileIntegrity(
            $fileData['path'], 
            $fileData['hash']
        );

        // Assert - Integrity verification
        $this->assertTrue($integrityResult['valid']);

        // Act - Generate secure URL
        $urlResult = $service->getSecureFileUrl($fileData['path'], 60);

        // Assert - URL generation
        $this->assertTrue($urlResult['success']);
        $this->assertArrayHasKey('url', $urlResult);
        $this->assertArrayHasKey('expires_at', $urlResult);

        // Act - Delete file
        $deleteResult = $service->deleteFile($fileData['path']);

        // Assert - Deletion
        $this->assertTrue($deleteResult['success']);
        $this->assertFalse(Storage::disk('local')->exists($fileData['path']));
    }

    public function test_file_validation_with_real_constraints()
    {
        // Arrange
        $service = app(FileUploadService::class);
        
        // Test valid files
        $validFiles = [
            UploadedFile::fake()->image('test.jpg')->size(1024),
            UploadedFile::fake()->image('test.png')->size(1024),
            UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf')
        ];

        foreach ($validFiles as $file) {
            $result = $service->validateFile($file);
            $this->assertTrue($result['valid'], "Failed for file: " . $file->getClientOriginalName());
        }

        // Test invalid files
        $invalidFiles = [
            UploadedFile::fake()->create('test.txt', 1024, 'text/plain'), // Wrong format
            UploadedFile::fake()->image('test.jpg')->size(3072), // Too large (3MB)
        ];

        foreach ($invalidFiles as $file) {
            $result = $service->validateFile($file);
            $this->assertFalse($result['valid'], "Should have failed for file: " . $file->getClientOriginalName());
        }
    }

    public function test_malicious_content_detection_integration()
    {
        // Arrange
        $service = app(FileUploadService::class);
        
        // Create a file with malicious content
        $maliciousContent = '<script>alert("XSS")</script><img src="x" onerror="alert(1)">';
        $tempFile = tmpfile();
        fwrite($tempFile, $maliciousContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $maliciousFile = new UploadedFile($tempPath, 'malicious.html', 'text/html', null, true);

        // Act
        $result = $service->scanForMaliciousContent($maliciousFile);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertEquals(FileUploadService::STATUS_MALICIOUS_CONTENT, $result['status']);

        // Cleanup
        fclose($tempFile);
    }

    public function test_storage_directory_creation()
    {
        // Arrange
        $service = app(FileUploadService::class);
        $file = UploadedFile::fake()->image('test.jpg')->size(1024);
        $customPath = 'custom/nested/upload/path';

        // Act
        $result = $service->storeFile($file, ['path' => $customPath]);

        // Assert
        $this->assertTrue($result['success']);
        $fileData = $result['file_data'];
        $this->assertStringContainsString($customPath, $fileData['path']);
        $this->assertTrue(Storage::disk('local')->exists($fileData['path']));
    }

    public function test_concurrent_file_uploads()
    {
        // Arrange
        $service = app(FileUploadService::class);
        
        // Simulate multiple concurrent uploads
        $results = [];
        $files = [
            UploadedFile::fake()->image('file1.jpg')->size(512),
            UploadedFile::fake()->image('file2.png')->size(768),
            UploadedFile::fake()->create('file3.pdf', 1024, 'application/pdf')
        ];

        // Act
        foreach ($files as $index => $file) {
            $results[] = $service->storeFile($file, [
                'prefix' => "concurrent_{$index}",
                'path' => 'concurrent-uploads'
            ]);
        }

        // Assert
        foreach ($results as $result) {
            $this->assertTrue($result['success']);
            $this->assertTrue(Storage::disk('local')->exists($result['file_data']['path']));
        }

        // Verify all files have unique names
        $storedNames = array_column(array_column($results, 'file_data'), 'stored_name');
        $uniqueNames = array_unique($storedNames);
        $this->assertCount(count($storedNames), $uniqueNames, 'All filenames should be unique');
    }

    public function test_error_handling_and_logging()
    {
        // Arrange
        $service = app(FileUploadService::class);
        
        // Test with invalid file path for deletion
        $deleteResult = $service->deleteFile('nonexistent/path/file.jpg');
        $this->assertFalse($deleteResult['success']);
        
        // Test with invalid file path for URL generation
        $urlResult = $service->getSecureFileUrl('nonexistent/path/file.jpg');
        $this->assertFalse($urlResult['success']);
        
        // Test with invalid file path for integrity verification
        $integrityResult = $service->verifyFileIntegrity('nonexistent/path/file.jpg', 'some_hash');
        $this->assertFalse($integrityResult['valid']);
    }

    public function test_cleanup_operations()
    {
        // Arrange
        $service = app(FileUploadService::class);
        
        // Create some temporary files
        $tempPath = FileUploadService::TEMP_PATH;
        $file1 = UploadedFile::fake()->image('temp1.jpg');
        $file2 = UploadedFile::fake()->image('temp2.jpg');
        
        Storage::disk('local')->putFileAs($tempPath, $file1, 'temp1.jpg');
        Storage::disk('local')->putFileAs($tempPath, $file2, 'temp2.jpg');

        // Act
        $cleanupResult = $service->cleanupTempFiles(0); // Clean all files

        // Assert
        $this->assertTrue($cleanupResult['success']);
        $this->assertArrayHasKey('deleted_count', $cleanupResult);
        $this->assertArrayHasKey('error_count', $cleanupResult);
    }
}