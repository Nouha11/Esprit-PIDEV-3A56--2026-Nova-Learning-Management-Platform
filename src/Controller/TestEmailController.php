<?php

namespace App\Controller;

use App\Entity\users\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class TestEmailController extends AbstractController
{
    #[Route('/test-email/{locale}', name: 'app_test_email', defaults: ['locale' => 'en'])]
    public function testEmail(string $locale, Environment $twig): Response
    {
        // Create a dummy user for testing
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('test@example.com');
        
        // Generate a test token
        $token = bin2hex(random_bytes(32));
        
        // Generate test verification URL
        $verificationUrl = $this->generateUrl(
            'app_verify_email',
            ['token' => $token],
            \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $expiresAt = new \DateTime('+24 hours');
        
        // Determine template based on locale
        $template = $locale === 'fr' 
            ? 'emails/verification_fr.html.twig' 
            : 'emails/verification_en.html.twig';
        
        // Render the email template
        $html = $twig->render($template, [
            'user' => $user,
            'verificationUrl' => $verificationUrl,
            'expiresAt' => $expiresAt,
        ]);
        
        return new Response($html);
    }
}
