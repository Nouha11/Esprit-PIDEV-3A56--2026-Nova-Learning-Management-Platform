<?php

namespace App\Service;

use App\Entity\users\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class PasswordResetService
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private Environment $twig,
        private string $fromEmail
    ) {
    }

    public function sendPasswordResetEmail(User $user, string $locale = 'en'): void
    {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $user->setResetPasswordToken($token);
        
        // Token expires in 1 hour
        $expiresAt = new \DateTime('+1 hour');
        $user->setResetPasswordTokenExpiresAt($expiresAt);

        // Generate reset URL
        $resetUrl = $this->urlGenerator->generate(
            'app_reset_password',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Determine subject and template based on locale
        $subject = $locale === 'fr' 
            ? 'Réinitialisation de votre mot de passe NOVA' 
            : 'Reset your NOVA password';

        $template = $locale === 'fr' 
            ? 'emails/password_reset_fr.html.twig' 
            : 'emails/password_reset_en.html.twig';

        // Render email content
        $htmlContent = $this->twig->render($template, [
            'user' => $user,
            'resetUrl' => $resetUrl,
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
}
