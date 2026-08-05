# FileUploadService Usage Guide

The FileUploadService provides secure file upload functionality with comprehensive validation, virus scanning, and file management capabilities.

## Features

- ✅ File format validation (JPEG, PNG, PDF)
- ✅ File size validation (max 2MB)
- ✅ Virus and malicious content detection
- ✅ Secure filename generation
- ✅ File integrity verification
- ✅ Automatic cleanup operations
- ✅ Comprehensive error handling and logging

## Basic Usage

### 1. Service Injection

The service is automatically registered as a singleton in the Laravel container:

```php
use App\Services\FileUploadService;

class PaymentController extends Controller
{
    public function __construct(
        private FileUploadService $fileUploadService
    ) {}
    
    // OR resolve from container
    public function someMethod()
    {
        $fileService = app(FileUploadService::class);
    }
}
```

### 2. File Validation

```php
use Illuminate\Http\UploadedFile;

public function validateUploadedFile(UploadedFile $file)
{
    $result = $this->fileUploadService->validateFile($file);
    
    if (!$result['valid']) {
        return response()->json([
            'error' => $result['message'],
            'status' => $result['status']
        ], 400);
    }
    
    return response()->json(['message' => 'File is valid']);
}
```

### 3. Secure File Upload

```php
public function uploadPaymentProof(Request $request)
{
    $file = $request->file('payment_proof');
    
    $result = $this->fileUploadService->storeFile($file, [
        'prefix' => 'payment',
        'path' => 'uploads/payment-proofs'
    ]);
    
    if (!$result['success']) {
        return response()->json([
            'error' => $result['message'],
            'status' => $result['status']
        ], 400);
    }
    
    $fileData = $result['file_data'];
    
    // Save file information to database
    Pembayaran::create([
        'bukti_pembayaran' => $fileData['stored_name'],
        'file_path' => $fileData['path'],
        'file_hash' => $fileData['hash'],
        // ... other fields
    ]);
    
    return response()->json([
        'message' => 'File uploaded successfully',
        'file' => [
            'name' => $fileData['stored_name'],
            'size' => $fileData['size'],
            'url' => $fileData['url']
        ]
    ]);
}
```

### 4. File Integrity Verification

```php
public function verifyPaymentProof($paymentId)
{
    $payment = Pembayaran::findOrFail($paymentId);
    
    $result = $this->fileUploadService->verifyFileIntegrity(
        $payment->file_path,
        $payment->file_hash
    );
    
    if (!$result['valid']) {
        // File has been tampered with or corrupted
        Log::warning('File integrity check failed', [
            'payment_id' => $paymentId,
            'file_path' => $payment->file_path
        ]);
        
        return response()->json([
            'error' => 'File integrity verification failed'
        ], 400);
    }
    
    return response()->json(['message' => 'File integrity verified']);
}
```

### 5. Generate Secure File URLs

```php
public function getPaymentProofUrl($paymentId)
{
    $payment = Pembayaran::findOrFail($paymentId);
    
    $result = $this->fileUploadService->getSecureFileUrl(
        $payment->file_path,
        60 // expires in 60 minutes
    );
    
    if (!$result['success']) {
        return response()->json(['error' => $result['message']], 404);
    }
    
    return response()->json([
        'url' => $result['url'],
        'expires_at' => $result['expires_at']
    ]);
}
```

### 6. File Deletion

```php
public function deletePaymentProof($paymentId)
{
    $payment = Pembayaran::findOrFail($paymentId);
    
    $result = $this->fileUploadService->deleteFile($payment->file_path);
    
    if ($result['success']) {
        $payment->delete();
        return response()->json(['message' => 'File deleted successfully']);
    }
    
    return response()->json([
        'error' => 'Failed to delete file: ' . $result['message']
    ], 500);
}
```

## Advanced Usage

### Custom File Validation

```php
public function uploadWithCustomValidation(UploadedFile $file)
{
    // Step-by-step validation for custom error handling
    
    // 1. Format validation
    $formatResult = $this->fileUploadService->validateFileFormat($file);
    if (!$formatResult['valid']) {
        return $this->handleFormatError($formatResult);
    }
    
    // 2. Size validation
    $sizeResult = $this->fileUploadService->validateFileSize($file);
    if (!$sizeResult['valid']) {
        return $this->handleSizeError($sizeResult);
    }
    
    // 3. Security scan
    $securityResult = $this->fileUploadService->scanForMaliciousContent($file);
    if (!$securityResult['valid']) {
        return $this->handleSecurityError($securityResult);
    }
    
    // Proceed with upload
    return $this->fileUploadService->storeFile($file);
}
```

### Batch File Cleanup

```php
// In a scheduled command or job
public function cleanupOldFiles()
{
    // Clean files older than 24 hours
    $result = $this->fileUploadService->cleanupTempFiles(24);
    
    Log::info('File cleanup completed', [
        'deleted_count' => $result['deleted_count'],
        'error_count' => $result['error_count']
    ]);
}
```

### Custom Storage Configuration

```php
// You can modify the service constants or create custom methods
class CustomFileUploadService extends FileUploadService
{
    public function storeInvoiceFile(UploadedFile $file)
    {
        return $this->storeFile($file, [
            'prefix' => 'invoice',
            'path' => 'invoices/' . date('Y/m')
        ]);
    }
}
```

## Error Handling

The service provides structured error responses:

```php
$result = $this->fileUploadService->validateFile($file);

switch ($result['status']) {
    case FileUploadService::STATUS_INVALID_FORMAT:
        // Handle format error
        break;
    case FileUploadService::STATUS_INVALID_SIZE:
        // Handle size error
        break;
    case FileUploadService::STATUS_VIRUS_DETECTED:
        // Handle virus detection
        break;
    case FileUploadService::STATUS_MALICIOUS_CONTENT:
        // Handle malicious content
        break;
    case FileUploadService::STATUS_UPLOAD_ERROR:
        // Handle upload error
        break;
}
```

## Configuration Constants

- `ALLOWED_FORMATS`: ['jpeg', 'jpg', 'png', 'pdf']
- `MAX_FILE_SIZE`: 2097152 (2MB)
- `STORAGE_DISK`: 'local'
- `UPLOAD_PATH`: 'uploads/payment-proofs'
- `TEMP_PATH`: 'temp/uploads'

## Security Features

1. **File Type Validation**: Validates both extension and MIME type
2. **Virus Scanning**: Detects common virus signatures and malicious patterns
3. **Content Analysis**: Scans for JavaScript, executable content, and suspicious patterns
4. **PDF Security**: Checks for potentially dangerous PDF elements
5. **Secure Naming**: Generates cryptographically secure filenames
6. **Hash Verification**: Provides file integrity checking

## Logging

The service automatically logs:
- Successful file uploads
- Security violations (virus/malicious content detection)
- File operations (deletion, access)
- Error conditions

All logs include relevant context for debugging and security monitoring.