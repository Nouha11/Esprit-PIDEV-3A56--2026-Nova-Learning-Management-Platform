<?php

namespace App\Service\Library;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Service pour gérer les uploads de fichiers localement
 */
class FileUploadService
{
    private string $uploadsDirectory;
    private SluggerInterface $slugger;

    public function __construct(string $uploadsDirectory, SluggerInterface $slugger)
    {
        $this->uploadsDirectory = $uploadsDirectory;
        $this->slugger = $slugger;
    }

    /**
     * Upload un PDF localement
     * 
     * @param UploadedFile $file Le fichier PDF à uploader
     * @return array Résultat de l'upload avec le chemin relatif
     */
    public function uploadPdf(UploadedFile $file): array
    {
        try {
            // Vérifier que le fichier est valide
            if (!$file->isValid()) {
                return [
                    'success' => false,
                    'error' => 'Invalid file upload'
                ];
            }

            // Vérifier que c'est un PDF
            if ($file->getMimeType() !== 'application/pdf') {
                return [
                    'success' => false,
                    'error' => 'File must be a PDF (got: ' . $file->getMimeType() . ')'
                ];
            }

            // Créer le dossier pdfs s'il n'existe pas
            $pdfDirectory = $this->uploadsDirectory . '/pdfs';
            if (!file_exists($pdfDirectory)) {
                mkdir($pdfDirectory, 0777, true);
            }

            // Générer un nom de fichier unique
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

            // Déplacer le fichier
            $file->move($pdfDirectory, $newFilename);

            return [
                'success' => true,
                'path' => 'uploads/pdfs/' . $newFilename,
                'filename' => $newFilename,
                'size' => filesize($pdfDirectory . '/' . $newFilename)
            ];
        } catch (FileException $e) {
            return [
                'success' => false,
                'error' => 'Failed to upload file: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload une image de couverture localement
     * 
     * @param UploadedFile $file Le fichier image à uploader
     * @return array Résultat de l'upload
     */
    public function uploadCoverImage(UploadedFile $file): array
    {
        try {
            // Créer le dossier books s'il n'existe pas
            $booksDirectory = $this->uploadsDirectory . '/books';
            if (!file_exists($booksDirectory)) {
                mkdir($booksDirectory, 0777, true);
            }

            // Générer un nom de fichier unique
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

            // Déplacer le fichier
            $file->move($booksDirectory, $newFilename);

            return [
                'success' => true,
                'path' => 'uploads/books/' . $newFilename,
                'filename' => $newFilename
            ];
        } catch (FileException $e) {
            return [
                'success' => false,
                'error' => 'Failed to upload image: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer un fichier
     * 
     * @param string $path Chemin relatif du fichier (ex: 'uploads/pdfs/file.pdf')
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        $fullPath = $this->uploadsDirectory . '/../' . $path;
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
}
