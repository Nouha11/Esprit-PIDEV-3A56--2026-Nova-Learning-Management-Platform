<?php

namespace App\Command\StudySession;

use App\Repository\StudySession\StudySessionRepository;
use App\Repository\UserRepository;
use App\Service\StudySession\NotificationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:study-session:send-weekly-reports',
    description: 'Send weekly progress reports to all users with completed sessions in the past week',
)]
class SendWeeklyReportsCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private StudySessionRepository $sessionRepository,
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Sending Weekly Progress Reports');
        
        // Calculate time window: past 7 days
        $now = new \DateTimeImmutable();
        $oneWeekAgo = $now->modify('-7 days');
        
        $io->writeln(sprintf('Checking for users with completed sessions from %s to %s', 
            $oneWeekAgo->format('Y-m-d H:i'),
            $now->format('Y-m-d H:i')
        ));
        
        // Query for users who have completed sessions in the past week
        $qb = $this->sessionRepository->createQueryBuilder('s')
            ->select('DISTINCT u.id')
            ->join('s.user', 'u')
            ->where('s.completedAt IS NOT NULL')
            ->andWhere('s.completedAt >= :oneWeekAgo')
            ->andWhere('s.completedAt <= :now')
            ->setParameter('oneWeekAgo', $oneWeekAgo)
            ->setParameter('now', $now);
        
        $userIds = array_column($qb->getQuery()->getResult(), 'id');
        
        if (empty($userIds)) {
            $io->success('No users with completed sessions in the past week. No reports sent.');
            return Command::SUCCESS;
        }
        
        $io->writeln(sprintf('Found %d user(s) with completed sessions in the past week.', count($userIds)));
        
        $sentCount = 0;
        $skippedCount = 0;
        
        foreach ($userIds as $userId) {
            try {
                $user = $this->userRepository->find($userId);
                
                if (!$user) {
                    $skippedCount++;
                    continue;
                }
                
                // Generate and send weekly progress report via NotificationService
                $this->notificationService->sendWeeklyProgressReport($user);
                $sentCount++;
                
                $io->writeln(sprintf(
                    '✓ Queued weekly report for user: %s (ID: %d)',
                    $user->getUsername(),
                    $user->getId()
                ));
            } catch (\Exception $e) {
                $skippedCount++;
                $io->error(sprintf(
                    'Failed to queue weekly report for user ID %d: %s',
                    $userId,
                    $e->getMessage()
                ));
            }
        }
        
        $io->newLine();
        $io->success(sprintf(
            'Weekly report processing complete. Sent: %d, Skipped: %d',
            $sentCount,
            $skippedCount
        ));
        
        return Command::SUCCESS;
    }
}
