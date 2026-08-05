<?php

namespace Tests\Unit;

use App\Services\FileUploadService;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Mockery;

class FileUploadServiceTest extends TestCase
{
    private FileUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FileUploadService();
        
        // Fake the storage for testing
        Storage::fake('local');
        Log::shouldReceive('info', 'warning', 'error')->byDefault();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_validates_valid_jpeg_format()
    {
        // Arrange
        $file = UploadedFile::fake()->image('payment.jpg', 800, 600)->mimeType('image/jpeg');

        // Act
        $result = $this->service->validateFileFormat($file);

        // Assert
        $this->assertTrue($result['valid']);
        $this->assertEquals(FileUploadService::STATUS_VALID, $result['status']);
        $this->assertEquals('jpg', $result['extension']);
        $this->assertEquals('image/jpeg', $result['mime_type']);
    }

    /** @test */
    public function it_validates_valid_png_format()
    {
        // Arrange
        $file = UploadedFile::fake()->image('payment.png', 800, 600)->mimeType('image/png');

        // Act
        $result = $this->service->validateFileFormat($file);

        // Assert
        $this->assertTrue($result['valid']);
        $this->assertEquals(FileUploadService::STATUS_VALID, $result['status']);
        $this->assertEquals('png', $result['extension']);
        $this->assertEquals('image/png', $result['mime_type']);
    }

    /** @test */
    public function it_validates_valid_pdf_format()
    {
        // Arrange
        $file = UploadedFile::fake()->create('receipt.pdf', 1024, 'application/pdf');

        // Act
        $result = $this->service->validateFileFormat($file);

        // Assert
        $this->assertTrue($result['valid']);
        $this->assertEquals(FileUploadService::STATUS_VALID, $result['status']);
        $this->assertEquals('pdf', $result['extension']);
        $this->assertEquals('application/pdf', $result['mime_type']);
    }

    /** @test */
    public function it_rejects_invalid_file_format()
    {
        // Arrange
        $file = UploadedFile::fake()->create('document.txt', 1024, 'text/plain');

        // Act
        $result = $this->service->validateFileFormat($file);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertEquals(FileUploadService::STATUS_INVALID_FORMAT, $result['status']);
        $this->assertStringContainsString('File format not allowed', $result['message']);
        $this->assertEquals(FileUploadService::ALLOWED_FORMATS, $result['allowed_formats']);
    }

    /** @test */
    public function it_rejects_mismatched_mime_type()
    {
        // Arrange - Create a file with .jpg extension but wrong MIME type
        $file = UploadedFile::fake()->create('image.jpg', 1024, 'text/plain');

        // Act
        $result = $this->service->validateFileFormat($file);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertEquals(FileUploadService::STATUS_INVALID_FORMAT, $result['status']);
        $this->assertStringContainsString('MIME type does not match extension', $result['message']);
        $this->assertEquals('text/plain', $result['detected_mime']);
    }

    /** @test */
    public function it_validates_file_size_within_limit()
    {
        // Arrange - 1MB file (within 2MB limit)
        $file = UploadedFile::fake()->image('payment.jpg')->size(1024);

        // Act
        $result = $this->service->validateFileSize($file);

        // Assert
        $this->assertTrue($result['valid']);
        $this->assertEquals(FileUploadService::STATUS_VALID, $result['status']);
        $this->assertEquals(1.0, $result['file_size']);
    }

    /** @test */
    public function it_rejects_oversized_files()
    {
        // Arrange - 3MB file (exceeds 2MB limit)
        $file = UploadedFile::fake()->image('large.jpg')->size(3072);

        // Act
        $result = $this->service->validateFileSize($file);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertEquals(FileUploadService::STATUS_INVALID_SIZE, $result['status']);
        $this->assertStringContainsString('exceeds maximum limit', $result['message']);
        $this->assertEquals(3.0, $result['file_size']);
        $this->assertEquals(2.0, $result['max_size']);
    }

    /** @test */
    public function it_detects_eicar_virus_signature()
    {
        // Arrange
        $virusContent = 'X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*';
        $tempFile = tmpfile();
        fwrite($tempFile, $virusContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $file = new UploadedFile($tempPath, 'test.txt', 'text/plain', null, true);

        // Act
        $result = $this->service->scanForMaliciousContent($file);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertEquals(FileUploadService::STATUS_VIRUS_DETECTED, $result['status']);
        $this->assertStringContainsString('virus detected', $result['message']);

        // Cleanup
        fclose($tempFile);
    }

    /** @test */
    public function it_detects_malicious_javascript_content()
    {
        // Arrange
        $maliciousContent = '<script>alert("XSS")</script>';
        $tempFile = tmpfile();
        fwrite($tempFile, $maliciousContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $file = new UploadedFile($tempPath, 'test.html', 'text/html', null, true);

        // Act
        $result = $this->service->scanForMaliciousContent($file);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertEquals(FileUploadService::STATUS_MALICIOUS_CONTENT, $result['status']);
        $this->assertStringContainsString('Malicious content detected', $result['message']);

        // Cleanup
        fclose($tempFile);
    }

    /** @test */
    public function it_passes_clean_file_scan()
    {
        // Arrange
        $cleanContent = 'This is a clean text file with no malicious content.';
        $tempFile = tmpfile();
        fwrite($tempFile, $cleanContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $file = new UploadedFile($tempPath, 'clean.txt', 'text/plain', null, true);

        // Act
        $result = $this->service->scanForMaliciousContent($file);

        // Assert
        $this->assertTrue($result['valid']);
        $this->assertEquals(FileUploadService::STATUS_VALID, $result['status']);
        $this->assertStringContainsString('passed security scan', $result['message']);

        // Cleanup
        fclose($tempFile);
    }

    /** @test */
    public function it_detects_suspicious_pdf_content()
    {
        // Arrange - PDF with JavaScript
        $maliciousPdfContent = '%PDF-1.4\n/JavaScript /JS\nsome pdf content';
        $tempFile = tmpfile();
        fwrite($tempFile, $maliciousPdfContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $file = new UploadedFile($tempPath, 'malicious.pdf', 'application/pdf', null, true);

        // Act
        $result = $this->service->scanForMaliciousContent($file);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertEquals(FileUploadService::STATUS_MALICIOUS_CONTENT, $result['status']);
        $this->assertStringContainsString('Suspicious content detected in PDF', $result['message']);

        // Cleanup
        fclose($tempFile);
    }

    /** @test */
    public function it_generates_secure_filename()
    {
        // Arrange
        $file = UploadedFile::fake()->image('payment proof.jpg');

        // Act
        $filename = $this->service->generateSecureFilename($file, 'payment');

        // Assert
        $this->assertStringStartsWith('payment_', $filename);
        $this->assertStringEndsWith('.jpg', $filename);
        $this->assertMatchesRegularExpression('/^payment_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}_[a-zA-Z0-9]{8}_[a-f0-9]{8}\.jpg$/', $filename);
    }

    /** @test */
    public function it_validates_file_successfully()
    {
        // Arrange
        $file = UploadedFile::fake()->image('payment.jpg', 800, 600)->size(1024);

        // Act
        $result = $this->service->validateFile($file);

        // Assert
        $this->assertTrue($result['valid']);
        $this->assertEquals(FileUploadService::STATUS_VALID, $result['status']);
        $this->assertEquals('File validation successful', $result['message']);
    }

    /** @test */
    public function it_rejects_invalid_uploaded_file()
    {
        // Arrange - Mock a file with upload error
        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('isValid')->andReturn(false);
        $file->shouldReceive('getErrorMessage')->andReturn('Upload failed');

        // Act
        $result = $this->service->validateFile($file);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertEquals(FileUploadService::STATUS_UPLOAD_ERROR, $result['status']);
        $this->assertStringContainsString('File upload error', $result['message']);
    }

    /** @test */
    public function it_stores_file_successfully()
    {
        // Arrange
        $file = UploadedFile::fake()->image('payment.jpg', 800, 600)->size(1024);

        // Act
        $result = $this->service->storeFile($file, ['prefix' => 'test']);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals(FileUploadService::STATUS_VALID, $result['status']);
        $this->assertEquals('File uploaded successfully', $result['message']);
        
        $fileData = $result['file_data'];
        $this->assertEquals('payment.jpg', $fileData['original_name']);
        $this->assertStringStartsWith('test_', $fileData['stored_name']);
        $this->assertEquals('jpg', $fileData['extension']);
        $this->assertEquals('image/jpeg', $fileData['mime_type']);
        $this->assertArrayHasKey('hash', $fileData);
        $this->assertArrayHasKey('uploaded_at', $fileData);
        
        // Verify file was stored
        $this->assertTrue(Storage::disk('local')->exists($fileData['path']));
    }

    /** @test */
    public function it_stores_file_with_default_path()
    {
        // Arrange
        $file = UploadedFile::fake()->image('payment.jpg')->size(1024);

        // Act
        $result = $this->service->storeFile($file);

        // Assert
        $this->assertTrue($result['success']);
        $fileData = $result['file_data'];
        $this->assertStringContainsString(FileUploadService::UPLOAD_PATH, $fileData['path']);
    }

    /** @test */
    public function it_fails_to_store_invalid_file()
    {
        // Arrange
        $file = UploadedFile::fake()->create('document.txt', 1024, 'text/plain');

        // Act
        $result = $this->service->storeFile($file);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals(FileUploadService::STATUS_INVALID_FORMAT, $result['status']);
    }

    /** @test */
    public function it_deletes_existing_file()
    {
        // Arrange
        $file = UploadedFile::fake()->image('test.jpg');
        $path = 'test/delete_test.jpg';
        Storage::disk('local')->putFileAs('test', $file, 'delete_test.jpg');

        // Act
        $result = $this->service->deleteFile($path);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('File deleted successfully', $result['message']);
        $this->assertFalse(Storage::disk('local')->exists($path));
    }

    /** @test */
    public function it_fails_to_delete_nonexistent_file()
    {
        // Arrange
        $path = 'nonexistent/file.jpg';

        // Act
        $result = $this->service->deleteFile($path);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('File does not exist', $result['message']);
    }

    /** @test */
    public function it_generates_secure_file_url()
    {
        // Arrange
        $file = UploadedFile::fake()->image('test.jpg');
        $path = 'test/url_test.jpg';
        Storage::disk('local')->putFileAs('test', $file, 'url_test.jpg');

        // Act
        $result = $this->service->getSecureFileUrl($path, 30);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('expires_at', $result);
        $this->assertEquals('File URL generated successfully', $result['message']);
    }

    /** @test */
    public function it_fails_to_generate_url_for_nonexistent_file()
    {
        // Arrange
        $path = 'nonexistent/file.jpg';

        // Act
        $result = $this->service->getSecureFileUrl($path);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('File does not exist', $result['message']);
    }

    /** @test */
    public function it_verifies_file_integrity_successfully()
    {
        // Arrange
        $content = 'test file content';
        $expectedHash = hash('sha256', $content);
        
        $file = UploadedFile::fake()->create('test.txt', strlen($content));
        $path = 'test/integrity_test.txt';
        
        // Store the file with known content
        Storage::disk('local')->put($path, $content);

        // Act
        $result = $this->service->verifyFileIntegrity($path, $expectedHash);

        // Assert
        $this->assertTrue($result['valid']);
        $this->assertEquals('File integrity verified', $result['message']);
    }

    /** @test */
    public function it_detects_file_integrity_failure()
    {
        // Arrange
        $content = 'test file content';
        $wrongHash = 'wrong_hash_value';
        
        $path = 'test/integrity_fail_test.txt';
        Storage::disk('local')->put($path, $content);

        // Act
        $result = $this->service->verifyFileIntegrity($path, $wrongHash);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertEquals('File integrity verification failed', $result['message']);
        $this->assertEquals($wrongHash, $result['expected_hash']);
        $this->assertArrayHasKey('actual_hash', $result);
    }

    /** @test */
    public function it_cleans_up_temp_files()
    {
        // Arrange
        $tempPath = FileUploadService::TEMP_PATH;
        
        // Create some old temp files
        $oldFile1 = UploadedFile::fake()->image('old1.jpg');
        $oldFile2 = UploadedFile::fake()->image('old2.jpg');
        
        Storage::disk('local')->putFileAs($tempPath, $oldFile1, 'old1.jpg');
        Storage::disk('local')->putFileAs($tempPath, $oldFile2, 'old2.jpg');
        
        // Mock the last modified time to be older than 24 hours
        Storage::shouldReceive('disk')->with('local')->andReturnSelf();
        Storage::shouldReceive('files')->with($tempPath)->andReturn([
            $tempPath . '/old1.jpg',
            $tempPath . '/old2.jpg'
        ]);
        Storage::shouldReceive('lastModified')->andReturn(now()->subHours(25)->timestamp);
        Storage::shouldReceive('delete')->andReturn(true);

        // Act
        $result = $this->service->cleanupTempFiles(24);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('deleted_count', $result);
        $this->assertArrayHasKey('error_count', $result);
    }

    /** @test */
    public function it_handles_constants_correctly()
    {
        // Test that constants are properly defined
        $this->assertEquals(['jpeg', 'jpg', 'png', 'pdf'], FileUploadService::ALLOWED_FORMATS);
        $this->assertEquals(2097152, FileUploadService::MAX_FILE_SIZE); // 2MB
        $this->assertEquals('local', FileUploadService::STORAGE_DISK);
        $this->assertEquals('uploads/payment-proofs', FileUploadService::UPLOAD_PATH);
        $this->assertEquals('temp/uploads', FileUploadService::TEMP_PATH);
    }

    /** @test */
    public function it_handles_edge_case_file_extensions()
    {
        // Test case-insensitive extension handling
        $file = UploadedFile::fake()->image('PAYMENT.JPG')->mimeType('image/jpeg');
        
        $result = $this->service->validateFileFormat($file);
        
        $this->assertTrue($result['valid']);
        $this->assertEquals('jpg', $result['extension']); // Should be lowercase
    }
}