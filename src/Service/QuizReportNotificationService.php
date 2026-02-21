<?php

namespace App\Service;

use App\Entity\Quiz\QuizReport;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class QuizReportNotificationService
{
    public function __construct(
        private MailerInterface $mailer,
        private UserRepository $userRepository,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface $logger,
        private Environment $twig,
        private string $adminEmail = 'admin@example.com'
    ) {
    }

    public function notifyAdminsOfNewReport(QuizReport $report): void
    {
        try {
            // Get all admin users
            $admins = $this->userRepository->findByRole('ROLE_ADMIN');
            
            // Generate the report URL
            $reportUrl = $this->urlGenerator->generate(
                'app_quiz_reports_show',
                ['id' => $report->getId(), 'prefix' => 'admin'],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            foreach ($admins as $admin) {
                $emailContent = $this->twig->render('emails/quiz_report_notification.html.twig', [
                    'report' => $report,
                    'quiz' => $report->getQuiz(),
                    'reporter' => $report->getReportedBy(),
                    'reportUrl' => $reportUrl,
                    'adminName' => $admin->getUsername()
                ]);

                $email = (new Email())
                    ->from($this->adminEmail)
                    ->to($admin->getEmail())
                    ->subject('🚨 New Quiz Report - Action Required')
                    ->html($emailContent);

                $this->mailer->send($email);
            }

            $this->logger->info('Quiz report notification sent', [
                'report_id' => $report->getId(),
                'quiz_id' => $report->getQuiz()->getId(),
                'admins_notified' => count($admins)
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to send quiz report notification', [
                'error' => $e->getMessage(),
                'report_id' => $report->getId()
            ]);
        }
    }
}
