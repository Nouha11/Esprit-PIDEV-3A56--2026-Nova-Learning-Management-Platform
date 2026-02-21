<?php

namespace App\Tests\Service\StudySession;

use App\Entity\StudySession\Resource;
use App\Entity\StudySession\StudySession;
use App\Service\StudySession\ResourceManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ResourceManagerTest extends TestCase
{
    private ResourceManager $resourceManager;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private string $uploadDirectory;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->uploadDirectory = sys_get_temp_dir() . '/test_uploads';
        
        // Create upload directory
        if (!is_dir($this->uploadDirectory)) {
            mkdir($this->uploadDirectory, 0755, true);
        }

        $this->resourceManager = new ResourceManager(
            $this->entityManager,
            $this->uploadDirectory,
            $this->logger
        );
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (is_dir($this->uploadDirectory)) {
            $files = glob($this->uploadDirectory . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->uploadDirectory);
        }
    }

    public function testValidatePDFWithValidFile(): void
    {
        // Create a temporary PDF file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_pdf_');
        file_put_contents($tempFile, '%PDF-1.4 test content');
        
        $uploadedFile = new UploadedFile(
            $tempFile,
            'test.pdf',
            'application/pdf',
            null,
            true
        );

        $result = $this->resourceManager->validatePDF($uploadedFile);
        
        $this->assertTrue($result);
    }

    public function testValidatePDFWithInvalidMimeType(): void
    {
        // Create a temporary file with wrong MIME type
        $tempFile = tempnam(sys_get_temp_dir(), 'test_txt_');
        file_put_contents($tempFile, 'test content');
        
        $uploadedFile = new UploadedFile(
            $tempFile,
            'test.txt',
            'text/plain',
            null,
            true
        );

        $result = $this->resourceManager->validatePDF($uploadedFile);
        
        $this->assertFalse($result);
    }

    public function testValidatePDFWithOversizedFile(): void
    {
        // Create a temporary PDF file larger than 10MB
        $tempFile = tempnam(sys_get_temp_dir(), 'test_large_pdf_');
        $largeContent = str_repeat('a', 11 * 1024 * 1024); // 11MB
        file_put_contents($tempFile, $largeContent);
        
        $uploadedFile = new UploadedFile(
            $tempFile,
            'large.pdf',
            'application/pdf',
            null,
            true
        );

        $result = $this->resourceManager->validatePDF($uploadedFile);
        
        $this->assertFalse($result);
        
        // Clean up
        unlink($tempFile);
    }

    public function testGetStoragePath(): void
    {
        $resource = new Resource();
        $resource->setStoredFilename('test-file.pdf');

        $path = $this->resourceManager->getStoragePath($resource);

        $expectedPath = $this->uploadDirectory . '/test-file.pdf';
        $this->assertEquals($expectedPath, $path);
    }

    public function testUploadPDFCreatesUniqueFilenames(): void
    {
        $session = $this->createMock(StudySession::class);
        $session->method('getId')->willReturn(1);

        // Create first file
        $tempFile1 = tempnam(sys_get_temp_dir(), 'test_pdf_1_');
        file_put_contents($tempFile1, '%PDF-1.4 test content 1');
        
        $uploadedFile1 = new UploadedFile(
            $tempFile1,
            'same-name.pdf',
            'application/pdf',
            null,
            true
        );

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');
        
        $this->entityManager->expects($this->exactly(2))
            ->method('flush');

        $resource1 = $this->resourceManager->uploadPDF($uploadedFile1, $session);

        // Create second file after first upload completes
        $tempFile2 = tempnam(sys_get_temp_dir(), 'test_pdf_2_');
        file_put_contents($tempFile2, '%PDF-1.4 test content 2');
        
        $uploadedFile2 = new UploadedFile(
            $tempFile2,
            'same-name.pdf',
            'application/pdf',
            null,
            true
        );

        $resource2 = $this->resourceManager->uploadPDF($uploadedFile2, $session);

        // Verify that stored filenames are unique
        $this->assertNotEquals(
            $resource1->getStoredFilename(),
            $resource2->getStoredFilename(),
            'Stored filenames should be unique even with same original filename'
        );
        
        // Verify both files have the same original filename
        $this->assertEquals('same-name.pdf', $resource1->getFilename());
        $this->assertEquals('same-name.pdf', $resource2->getFilename());
    }
}
