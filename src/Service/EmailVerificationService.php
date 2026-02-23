<?php

namespace App\Service;

use App\Entity\users\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class EmailVerificationService
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private Environment $twig,
        private string $fromEmail
    ) {
    }

    public function sendVerificationEmail(User $user, string $locale = 'en'): void
    {
        // Generate verification token
        $token = bin2hex(random_bytes(32));
        $user->setVerificationToken($token);
        
        // Token expires in 24 hours
        $expiresAt = new \DateTime('+24 hours');
        $user->setVerificationTokenExpiresAt($expiresAt);

        // Generate verification URL
        $verificationUrl = $this->urlGenerator->generate(
            'app_verify_email',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Determine subject and template based on locale
        $subject = $locale === 'fr' 
            ? 'Confirmez votre inscription à NOVA' 
            : 'Confirm your NOVA registration';

        $template = $locale === 'fr' 
            ? 'emails/verification_fr.html.twig' 
            : 'emails/verification_en.html.twig';

        // Render email content
        $htmlContent = $this->twig->render($template, [
            'user' => $user,
            'verificationUrl' => $verificationUrl,
            'expiresAt' => $expiresAt,
        ]);

        // Create and send email
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($user->getEmail())
            ->subject($subject)
            ->html($htmlContent);

        $this->mailer->send($email);
    }

    public function verifyToken(string $token): ?User
    {
        // This will be implemented in the controller using UserRepository
        return null;
    }
}
