<?php

namespace App\Command;

use App\Repository\StudentProfileRepository;
use App\Service\game\LevelCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fix-student-levels',
    description: 'Recalculate and fix all student levels based on their current XP'
)]
class FixStudentLevelsCommand extends Command
{
    public function __construct(
        private StudentProfileRepository $studentRepository,
        private LevelCalculatorService $levelCalculatorService,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Fixing Student Levels');
        $io->text('Recalculating levels based on current XP...');

        // Get all student profiles
        $students = $this->studentRepository->findAll();
        
        if (empty($students)) {
            $io->warning('No student profiles found.');
            return Command::SUCCESS;
        }

        $io->progressStart(count($students));
        
        $fixed = 0;
        $unchanged = 0;
        
        foreach ($students as $student) {
            $currentXP = $student->getTotalXP();
            $currentLevel = $student->getLevel();
            
            // Calculate correct level based on XP
            $levelInfo = $this->levelCalculatorService->calculateLevel($currentXP);
            $correctLevel = $levelInfo['level'];
            
            if ($currentLevel !== $correctLevel) {
                $student->setLevel($correctLevel);
                $fixed++;
                
                $io->text(sprintf(
                    'Fixed: Student #%d - XP: %d, Old Level: %d → New Level: %d (%s)',
                    $student->getId(),
                    $currentXP,
                    $currentLevel,
                    $correctLevel,
                    $levelInfo['name']
                ));
            } else {
                $unchanged++;
            }
            
            $io->progressAdvance();
        }
        
        // Flush all changes
        $this->entityManager->flush();
        
        $io->progressFinish();
        
        $io->newLine();
        $io->success([
            sprintf('Fixed %d student level(s)', $fixed),
            sprintf('%d student level(s) were already correct', $unchanged),
            sprintf('Total students processed: %d', count($students))
        ]);

        return Command::SUCCESS;
    }
}
