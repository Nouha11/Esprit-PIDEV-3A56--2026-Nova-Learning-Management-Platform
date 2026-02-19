<?php

namespace App\Service\game;

use App\Entity\Gamification\Reward;
use App\Entity\users\StudentProfile;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class CertificateService
{
    public function __construct(
        private Pdf $pdf,
        private Environment $twig
    ) {
    }

    /**
     * Generate a PDF certificate for a reward
     */
    public function generateCertificate(StudentProfile $student, Reward $reward, \DateTimeInterface $earnedDate): Response
    {
        // Render the HTML template
        $html = $this->twig->render('front/game/certificate.html.twig', [
            'student' => $student,
            'reward' => $reward,
            'earnedDate' => $earnedDate,
        ]);

        // Generate PDF from HTML
        $pdfContent = $this->pdf->getOutputFromHtml($html, [
            'page-size' => 'A4',
            'orientation' => 'Landscape',
            'margin-top' => '0mm',
            'margin-right' => '0mm',
            'margin-bottom' => '0mm',
            'margin-left' => '0mm',
            'encoding' => 'UTF-8',
            'enable-local-file-access' => true,
            'dpi' => 300,
            'image-dpi' => 300,
            'image-quality' => 100,
            'disable-smart-shrinking' => true,
            'zoom' => 1.0,
            'viewport-size' => '1280x1024',
        ]);

        // Create response with PDF
        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        
        // Generate filename
        $filename = $this->generateFilename($student, $reward);
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * Generate a clean filename for the certificate
     */
    private function generateFilename(StudentProfile $student, Reward $reward): string
    {
        $studentName = $this->sanitizeFilename($student->getFirstName() . '_' . $student->getLastName());
        $rewardName = $this->sanitizeFilename($reward->getName());
        $date = (new \DateTime())->format('Y-m-d');
        
        return "Certificate_{$studentName}_{$rewardName}_{$date}.pdf";
    }

    /**
     * Sanitize string for use in filename
     */
    private function sanitizeFilename(string $string): string
    {
        // Remove special characters and replace spaces with underscores
        $string = preg_replace('/[^A-Za-z0-9\-_]/', '_', $string);
        // Remove multiple underscores
        $string = preg_replace('/_+/', '_', $string);
        // Trim underscores from start and end
        return trim($string, '_');
    }
}
