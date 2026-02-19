<?php

namespace App\Command;

use App\Repository\StudentProfileRepository;
use App\Repository\Gamification\RewardRepository;
use App\Service\game\RewardNotificationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-reward-email',
    description: 'Test sending a reward notification email to a student',
)]
class TestRewardEmailCommand extends Command
{
    public function __construct(
        private RewardNotificationService $notificationService,
        private StudentProfileRepository $studentRepository,
        private RewardRepository $rewardRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('studentId', InputArgument::OPTIONAL, 'Student Profile ID (leave empty to see list)')
            ->addArgument('rewardId', InputArgument::OPTIONAL, 'Reward ID (leave empty to see list)')
            ->setHelp('This command allows you to test the reward email notification system.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $studentId = $input->getArgument('studentId');
        $rewardId = $input->getArgument('rewardId');

        // If no arguments, show lists
        if (!$studentId || !$rewardId) {
            $this->showLists($io);
            return Command::SUCCESS;
        }

        // Find student
        $student = $this->studentRepository->find($studentId);
        if (!$student) {
            $io->error("Student with ID {$studentId} not found!");
            return Command::FAILURE;
        }

        // Check if student has email
        if (!$student->getEmail()) {
            $io->error("Student '{$student->getFirstName()} {$student->getLastName()}' does not have an email address!");
            $io->note("You can update the email in the database or through the admin panel.");
            return Command::FAILURE;
        }

        // Find reward
        $reward = $this->rewardRepository->find($rewardId);
        if (!$reward) {
            $io->error("Reward with ID {$rewardId} not found!");
            return Command::FAILURE;
        }

        // Display info
        $io->section('Sending Test Email');
        $io->table(
            ['Field', 'Value'],
            [
                ['Student', "{$student->getFirstName()} {$student->getLastName()}"],
                ['Email', $student->getEmail()],
                ['Level', $student->getLevel()],
                ['XP', $student->getTotalXP()],
                ['Tokens', $student->getTotalTokens()],
                ['Reward', $reward->getName()],
                ['Type', $reward->getType()],
            ]
        );

        try {
            $this->notificationService->sendRewardUnlockedEmail($student, $reward);
            $io->success("Email sent successfully to {$student->getEmail()}!");
            $io->note("Check your email inbox (or spam folder) for the reward notification.");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error("Failed to send email: " . $e->getMessage());
            $io->note("Make sure your MAILER_DSN is configured correctly in .env file");
            return Command::FAILURE;
        }
    }

    private function showLists(SymfonyStyle $io): void
    {
        $io->title('Test Reward Email Notification');
        
        // Show students
        $students = $this->studentRepository->findAll();
        if (empty($students)) {
            $io->warning('No students found in the database!');
        } else {
            $io->section('Available Students');
            $studentData = [];
            foreach ($students as $student) {
                $studentData[] = [
                    $student->getId(),
                    $student->getFirstName() . ' ' . $student->getLastName(),
                    $student->getEmail() ?: '(no email)',
                    $student->getLevel(),
                    $student->getTotalXP(),
                    $student->getTotalTokens(),
                ];
            }
            $io->table(
                ['ID', 'Name', 'Email', 'Level', 'XP', 'Tokens'],
                $studentData
            );
        }

        // Show rewards
        $rewards = $this->rewardRepository->findAll();
        if (empty($rewards)) {
            $io->warning('No rewards found in the database!');
        } else {
            $io->section('Available Rewards');
            $rewardData = [];
            foreach ($rewards as $reward) {
                $rewardData[] = [
                    $reward->getId(),
                    $reward->getName(),
                    $reward->getType(),
                    $reward->getDescription() ? substr($reward->getDescription(), 0, 50) . '...' : '',
                ];
            }
            $io->table(
                ['ID', 'Name', 'Type', 'Description'],
                $rewardData
            );
        }

        $io->info('Usage: php bin/console app:test-reward-email <studentId> <rewardId>');
        $io->note('Example: php bin/console app:test-reward-email 1 1');
    }
}
