<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-verification-email',
    description: 'Test sending a verification email to a specific user',
)]
class TestVerificationEmailCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailVerificationService $emailService,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email address')
            ->setHelp('This command allows you to test sending a verification email to a specific user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $io->error("No user found with email: {$email}");
            return Command::FAILURE;
        }

        $io->info("Found user: {$user->getUsername()} ({$user->getEmail()})");
        $io->info("Current verification status: " . ($user->isVerified() ? 'Verified' : 'Not verified'));

        try {
            $io->info('Sending verification email...');
            $this->emailService->sendVerificationEmail($user, 'en');
            $this->entityManager->flush();
            
            $io->success('Verification email sent successfully!');
            $io->info("Verification token: {$user->getVerificationToken()}");
            $io->info("Token expires at: {$user->getVerificationTokenExpiresAt()->format('Y-m-d H:i:s')}");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to send verification email: ' . $e->getMessage());
            $io->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
