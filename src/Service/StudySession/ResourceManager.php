<?php

namespace App\Service\StudySession;

use App\Entity\StudySession\Resource;
use App\Entity\StudySession\StudySession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Psr\Log\LoggerInterface;

class ResourceManager
{
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB in bytes
    private const ALLOWED_MIME_TYPE = 'application/pdf';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private string $uploadDirectory,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Upload a PDF file and create a Resource entity
     * 
     * @param UploadedFile $file The uploaded file
     * @param StudySession $session The study session to associate with
     * @return Resource The created resource entity
     * @throws \InvalidArgumentException If validation fails
     * @throws FileException If file storage fails
     */
    public function uploadPDF(UploadedFile $file, StudySession $session): Resource
    {
        // Validate the PDF file
        if (!$this->validatePDF($file)) {
            throw new \InvalidArgumentException('Invalid PDF file');
        }

        // Generate unique filename
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        
        // Sanitize filename - remove special characters and convert to lowercase
        $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalFilename);
        $safeFilename = strtolower($safeFilename);
        
        $storedFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            // Ensure upload directory exists
            if (!is_dir($this->uploadDirectory)) {
                mkdir($this->uploadDirectory, 0755, true);
            }

            // Move the file to the upload directory
            $file->move($this->uploadDirectory, $storedFilename);

            // Create Resource entity
            $resource = new Resource();
            $resource->setFilename($file->getClientOriginalName());
            $resource->setStoredFilename($storedFilename);
            $resource->setFileSize($file->getSize());
            $resource->setMimeType($file->getMimeType());
            $resource->setStudySession($session);

            // Persist the resource
            $this->entityManager->persist($resource);
            $this->entityManager->flush();

            $this->logger->info('PDF uploaded successfully', [
                'filename' => $storedFilename,
                'session_id' => $session->getId()
            ]);

            return $resource;
        } catch (FileException $e) {
            $this->logger->error('Failed to upload PDF', [
                'error' => $e->getMessage(),
                'filename' => $file->getClientOriginalName()
            ]);
            throw new FileException('Failed to store the file: ' . $e->getMessage());
        }
    }

    /**
     * Delete a PDF file and its Resource entity
     * 
     * @param Resource $resource The resource to delete
     * @return void
     */
    public function deletePDF(Resource $resource): void
    {
        $storedFilename = $resource->getStoredFilename();
        $filePath = $this->getStoragePath($resource);

        try {
            // Remove the file from storage
            if (file_exists($filePath)) {
                unlink($filePath);
                $this->logger->info('PDF file deleted', ['filename' => $storedFilename]);
            }

            // Remove the resource entity
            $this->entityManager->remove($resource);
            $this->entityManager->flush();

            $this->logger->info('Resource entity deleted', ['id' => $resource->getId()]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete PDF', [
                'error' => $e->getMessage(),
                'filename' => $storedFilename
            ]);
            throw $e;
        }
    }

    /**
     * Validate a PDF file
     * 
     * @param UploadedFile $file The file to validate
     * @return bool True if valid, false otherwise
     */
    public function validatePDF(UploadedFile $file): bool
    {
        // Check if file is valid
        if (!$file->isValid()) {
            $this->logger->warning('Invalid file upload', [
                'error' => $file->getErrorMessage()
            ]);
            return false;
        }

        // Check MIME type
        if ($file->getMimeType() !== self::ALLOWED_MIME_TYPE) {
            $this->logger->warning('Invalid MIME type', [
                'expected' => self::ALLOWED_MIME_TYPE,
                'actual' => $file->getMimeType()
            ]);
            return false;
        }

        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            $this->logger->warning('File size exceeds limit', [
                'size' => $file->getSize(),
                'limit' => self::MAX_FILE_SIZE
            ]);
            return false;
        }

        return true;
    }

    /**
     * Get the full storage path for a resource
     * 
     * @param Resource $resource The resource
     * @return string The full file path
     */
    public function getStoragePath(Resource $resource): string
    {
        return $this->uploadDirectory . '/' . $resource->getStoredFilename();
    }
}
