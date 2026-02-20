<?php

namespace App\Command\StudySession;

use App\Repository\StudySession\StudyStreakRepository;
use App\Service\StudySession\StreakService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:study-session:check-streaks',
    description: 'Check all active streaks and reset those with 24-hour gaps',
)]
class CheckStreaksCommand extends Command
{
    public function __construct(
        private StudyStreakRepository $streakRepository,
        private StreakService $streakService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Checking Study Streaks');
        
        // Query all users with active streaks (currentStreak > 0)
        $qb = $this->streakRepository->createQueryBuilder('s')
            ->where('s.currentStreak > 0')
            ->orderBy('s.currentStreak', 'DESC');
        
        $streaks = $qb->getQuery()->getResult();
        
        if (empty($streaks)) {
            $io->success('No active streaks found. Nothing to check.');
            return Command::SUCCESS;
        }
        
        $io->writeln(sprintf('Found %d active streak(s) to check.', count($streaks)));
        
        $resetCount = 0;
        $maintainedCount = 0;
        
        foreach ($streaks as $streak) {
            $user = $streak->getUser();
            $previousStreak = $streak->getCurrentStreak();
            
            try {
                // Check for 24-hour gaps and reset streaks via StreakService
                $this->streakService->checkAndResetStreak($user);
                
                // Refresh the streak entity to get updated values
                $this->streakRepository->getEntityManager()->refresh($streak);
                $currentStreak = $streak->getCurrentStreak();
                
                if ($currentStreak === 0 && $previousStreak > 0) {
                    $resetCount++;
                    $io->writeln(sprintf(
                        '✗ Reset streak for user: %s (was %d days, last study: %s)',
                        $user->getUsername(),
                        $previousStreak,
                        $streak->getLastStudyDate()?->format('Y-m-d') ?? 'N/A'
                    ));
                } else {
                    $maintainedCount++;
                    $io->writeln(sprintf(
                        '✓ Maintained streak for user: %s (%d days, last study: %s)',
                        $user->getUsername(),
                        $currentStreak,
                        $streak->getLastStudyDate()?->format('Y-m-d') ?? 'N/A'
                    ));
                }
            } catch (\Exception $e) {
                $io->error(sprintf(
                    'Failed to check streak for user %s: %s',
                    $user->getUsername(),
                    $e->getMessage()
                ));
            }
        }
        
        $io->newLine();
        $io->success(sprintf(
            'Streak check complete. Maintained: %d, Reset: %d',
            $maintainedCount,
            $resetCount
        ));
        
        return Command::SUCCESS;
    }
}
