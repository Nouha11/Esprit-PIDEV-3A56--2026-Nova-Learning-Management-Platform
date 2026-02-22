<?php

namespace App\Command;

use App\Service\SessionManagementService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cleanup-sessions',
    description: 'Cleanup old inactive sessions (older than 30 days)',
)]
class CleanupSessionsCommand extends Command
{
    public function __construct(
        private SessionManagementService $sessionManagementService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Cleaning up old sessions');

        try {
            $count = $this->sessionManagementService->cleanupOldSessions();
            
            $io->success("Successfully deactivated {$count} old session(s)");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to cleanup sessions: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
