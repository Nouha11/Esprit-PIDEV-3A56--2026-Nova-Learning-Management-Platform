<?php

namespace App\Command\StudySession;

use App\Repository\StudySession\StudySessionRepository;
use App\Service\StudySession\NotificationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:study-session:send-reminders',
    description: 'Send email reminders for study sessions scheduled in the next 30 minutes',
)]
class SendSessionRemindersCommand extends Command
{
    public function __construct(
        private StudySessionRepository $sessionRepository,
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Sending Study Session Reminders');
        
        // Calculate time window: now to 30 minutes from now
        $now = new \DateTimeImmutable();
        $thirtyMinutesFromNow = $now->modify('+30 minutes');
        
        // Query for sessions scheduled in the next 30 minutes
        // Sessions should not be completed yet (completedAt is null)
        $qb = $this->sessionRepository->createQueryBuilder('s')
            ->where('s.startedAt >= :now')
            ->andWhere('s.startedAt <= :thirtyMinutes')
            ->andWhere('s.completedAt IS NULL')
            ->setParameter('now', $now)
            ->setParameter('thirtyMinutes', $thirtyMinutesFromNow)
            ->orderBy('s.startedAt', 'ASC');
        
        $sessions = $qb->getQuery()->getResult();
        
        if (empty($sessions)) {
            $io->success('No sessions scheduled in the next 30 minutes. No reminders sent.');
            return Command::SUCCESS;
        }
        
        $io->writeln(sprintf('Found %d session(s) scheduled in the next 30 minutes.', count($sessions)));
        
        $sentCount = 0;
        $skippedCount = 0;
        
        foreach ($sessions as $session) {
            try {
                // Queue email job via NotificationService
                $this->notificationService->sendSessionReminder($session);
                $sentCount++;
                
                $io->writeln(sprintf(
                    '✓ Queued reminder for session #%d (User: %s, Scheduled: %s)',
                    $session->getId(),
                    $session->getUser()->getUsername(),
                    $session->getStartedAt()->format('Y-m-d H:i')
                ));
            } catch (\Exception $e) {
                $skippedCount++;
                $io->error(sprintf(
                    'Failed to queue reminder for session #%d: %s',
                    $session->getId(),
                    $e->getMessage()
                ));
            }
        }
        
        $io->newLine();
        $io->success(sprintf(
            'Reminder processing complete. Sent: %d, Skipped: %d',
            $sentCount,
            $skippedCount
        ));
        
        return Command::SUCCESS;
    }
}
