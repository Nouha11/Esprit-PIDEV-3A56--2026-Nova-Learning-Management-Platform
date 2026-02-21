<?php

namespace App\Command;

use App\Repository\QuizRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-quiz-filtering',
    description: 'Test the quiz filtering and sorting functionality',
)]
class TestQuizFilteringCommand extends Command
{
    public function __construct(
        private QuizRepository $quizRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Testing Quiz Filtering and Sorting');

        // Test 1: Get all quizzes
        $io->section('Test 1: All Quizzes (No Filters)');
        $allQuizzes = $this->quizRepository->findWithFiltersAndSort();
        $io->text(sprintf('Found %d quizzes', count($allQuizzes)));
        foreach ($allQuizzes as $quiz) {
            $io->text(sprintf('  - %s (%d questions)', $quiz->getTitle(), $quiz->getQuestions()->count()));
        }

        // Test 2: Sort by question count
        $io->section('Test 2: Sort by Question Count (DESC)');
        $sortedQuizzes = $this->quizRepository->findWithFiltersAndSort([], 'questionCount', 'DESC');
        foreach ($sortedQuizzes as $quiz) {
            $io->text(sprintf('  - %s (%d questions)', $quiz->getTitle(), $quiz->getQuestions()->count()));
        }

        // Test 3: Filter by minimum questions
        $io->section('Test 3: Filter by Min Questions (>= 3)');
        $filteredQuizzes = $this->quizRepository->findWithFiltersAndSort(['minQuestions' => 3]);
        $io->text(sprintf('Found %d quizzes with >= 3 questions', count($filteredQuizzes)));
        foreach ($filteredQuizzes as $quiz) {
            $io->text(sprintf('  - %s (%d questions)', $quiz->getTitle(), $quiz->getQuestions()->count()));
        }

        // Test 4: Search by title
        $io->section('Test 4: Search by Title');
        $searchTerm = $io->ask('Enter search term (or press Enter to skip)', '');
        if ($searchTerm) {
            $searchResults = $this->quizRepository->findWithFiltersAndSort(['search' => $searchTerm]);
            $io->text(sprintf('Found %d quizzes matching "%s"', count($searchResults), $searchTerm));
            foreach ($searchResults as $quiz) {
                $io->text(sprintf('  - %s', $quiz->getTitle()));
            }
        }

        // Test 5: Statistics
        $io->section('Test 5: Quiz Statistics');
        $stats = $this->quizRepository->getQuizStatistics();
        $io->table(
            ['Metric', 'Value'],
            [
                ['Total Quizzes', $stats['totalQuizzes']],
                ['Min Questions', $stats['minQuestions']],
                ['Max Questions', $stats['maxQuestions']],
                ['Avg Questions', $stats['avgQuestions']],
            ]
        );

        $io->success('All tests completed successfully!');

        return Command::SUCCESS;
    }
}
