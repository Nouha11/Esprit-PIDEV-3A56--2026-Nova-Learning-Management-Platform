<?php

namespace App\Command;

use App\Entity\Gamification\Reward;
use App\Entity\users\StudentProfile;
use App\Repository\Gamification\RewardRepository;
use App\Repository\StudentProfileRepository;
use App\Service\game\CertificateService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-certificate',
    description: 'Test PDF certificate generation',
)]
class TestCertificateCommand extends Command
{
    public function __construct(
        private CertificateService $certificateService,
        private StudentProfileRepository $studentRepository,
        private RewardRepository $rewardRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Testing PDF Certificate Generation');

        // Get first student
        $student = $this->studentRepository->findOneBy([]);
        if (!$student) {
            $io->error('No student found in database. Please create a student first.');
            return Command::FAILURE;
        }

        // Get first badge or achievement reward
        $reward = $this->rewardRepository->findOneBy(['type' => 'BADGE']);
        if (!$reward) {
            $reward = $this->rewardRepository->findOneBy(['type' => 'ACHIEVEMENT']);
        }

        if (!$reward) {
            $io->error('No Badge or Achievement reward found. Please create one first.');
            return Command::FAILURE;
        }

        $io->section('Test Data');
        $io->table(
            ['Field', 'Value'],
            [
                ['Student', $student->getFirstName() . ' ' . $student->getLastName()],
                ['Reward', $reward->getName()],
                ['Type', $reward->getType()],
            ]
        );

        try {
            $io->section('Generating Certificate...');
            
            $earnedDate = new \DateTime();
            $response = $this->certificateService->generateCertificate($student, $reward, $earnedDate);
            
            // Save to file for testing
            $filename = 'test_certificate_' . date('Y-m-d_His') . '.pdf';
            $filepath = 'public/uploads/' . $filename;
            
            // Create directory if it doesn't exist
            if (!is_dir('public/uploads')) {
                mkdir('public/uploads', 0777, true);
            }
            
            file_put_contents($filepath, $response->getContent());
            
            $io->success([
                'Certificate generated successfully!',
                'Saved to: ' . $filepath,
                'You can open this file to view the certificate.'
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error([
                'Failed to generate certificate',
                'Error: ' . $e->getMessage(),
                '',
                'Common issues:',
                '1. wkhtmltopdf not installed or not in PATH',
                '2. Incorrect binary path in config/packages/knp_snappy.yaml',
                '3. Missing permissions',
                '',
                'Please check PDF_CERTIFICATE_SETUP.md for troubleshooting.'
            ]);

            return Command::FAILURE;
        }
    }
}
