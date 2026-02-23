<?php

namespace App\Command\Quiz;

use App\Repository\Quiz\QuizReportRepository;
use App\Service\Quiz\QuizReportNotificationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-quiz-report-notification',
    description: 'Test the quiz report notification system by sending an email for the latest report',
)]
class TestQuizReportNotificationCommand extends Command
{
    public function __construct(
        private QuizReportRepository $reportRepository,
        private QuizReportNotificationService $notificationService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Testing Quiz Report Notification System');

        // Get the latest report
        $reports = $this->reportRepository->findBy([], ['createdAt' => 'DESC'], 1);

        if (empty($reports)) {
            $io->error('No reports found in the database. Please create a report first.');
            return Command::FAILURE;
        }

        $report = $reports[0];

        $io->section('Report Details');
        $io->table(
            ['Property', 'Value'],
            [
                ['ID', $report->getId()],
                ['Quiz', $report->getQuiz()->getTitle()],
                ['Reported By', $report->getReportedBy()->getUsername()],
                ['Reason', $report->getReason()],
                ['Status', $report->getStatus()],
                ['Created At', $report->getCreatedAt()->format('Y-m-d H:i:s')],
            ]
        );

        $io->section('Sending Notification');
        
        try {
            $this->notificationService->notifyAdminsOfNewReport($report);
            $io->success('Notification sent successfully! Check admin email inboxes.');
        } catch (\Exception $e) {
            $io->error('Failed to send notification: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
