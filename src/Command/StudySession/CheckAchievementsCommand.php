<?php

namespace App\Command\StudySession;

use App\Repository\StudySession\StudyStreakRepository;
use App\Service\StudySession\NotificationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:study-session:check-achievements',
    description: 'Check for users who reached milestone streaks (7, 30, or 100 days) and send achievement notifications',
)]
class CheckAchievementsCommand extends Command
{
    private const MILESTONE_STREAKS = [7, 30, 100];
    
    public function __construct(
        private StudyStreakRepository $streakRepository,
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Checking Study Streak Achievements');
        
        $io->writeln(sprintf('Checking for milestone streaks: %s days', implode(', ', self::MILESTONE_STREAKS)));
        
        $totalNotifications = 0;
        
        foreach (self::MILESTONE_STREAKS as $milestone) {
            $io->section(sprintf('Checking %d-day streak milestone', $milestone));
            
            // Query users who reached this exact milestone
            // We check if currentStreak equals the milestone to avoid sending duplicate notifications
            $qb = $this->streakRepository->createQueryBuilder('s')
                ->where('s.currentStreak = :milestone')
                ->setParameter('milestone', $milestone);
            
            $streaks = $qb->getQuery()->getResult();
            
            if (empty($streaks)) {
                $io->writeln(sprintf('No users reached %d-day streak today.', $milestone));
                continue;
            }
            
            $io->writeln(sprintf('Found %d user(s) who reached %d-day streak.', count($streaks), $milestone));
            
            $sentCount = 0;
            $skippedCount = 0;
            
            foreach ($streaks as $streak) {
                $user = $streak->getUser();
                
                try {
                    // Send achievement notification via NotificationService
                    $this->notificationService->sendAchievementNotification($user, (string)$milestone);
                    $sentCount++;
                    $totalNotifications++;
                    
                    $io->writeln(sprintf(
                        '✓ Queued %d-day achievement notification for user: %s',
                        $milestone,
                        $user->getUsername()
                    ));
                } catch (\Exception $e) {
                    $skippedCount++;
                    $io->error(sprintf(
                        'Failed to queue achievement notification for user %s: %s',
                        $user->getUsername(),
                        $e->getMessage()
                    ));
                }
            }
            
            if ($sentCount > 0 || $skippedCount > 0) {
                $io->writeln(sprintf('Milestone %d: Sent: %d, Skipped: %d', $milestone, $sentCount, $skippedCount));
            }
        }
        
        $io->newLine();
        $io->success(sprintf(
            'Achievement check complete. Total notifications queued: %d',
            $totalNotifications
        ));
        
        return Command::SUCCESS;
    }
}
