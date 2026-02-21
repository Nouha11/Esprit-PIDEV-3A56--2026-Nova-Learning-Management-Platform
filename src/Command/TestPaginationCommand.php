<?php

namespace App\Command;

use App\Repository\QuizRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-pagination',
    description: 'Test quiz pagination functionality'
)]
class TestPaginationCommand extends Command
{
    public function __construct(
        private QuizRepository $quizRepository,
        private PaginatorInterface $paginator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Testing Quiz Pagination');
        
        // Get query builder
        $queryBuilder = $this->quizRepository->findWithFiltersAndSort([], 'title', 'ASC');
        
        // Test pagination with 5 items per page
        $pagination = $this->paginator->paginate(
            $queryBuilder,
            1, // page number
            5  // items per page
        );
        
        $io->section('Pagination Results');
        $io->table(
            ['Property', 'Value'],
            [
                ['Total Items', $pagination->getTotalItemCount()],
                ['Items Per Page', $pagination->getItemNumberPerPage()],
                ['Current Page', $pagination->getCurrentPageNumber()],
                ['Total Pages', ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage())],
            ]
        );
        
        $io->section('Quizzes on Page 1');
        foreach ($pagination as $quiz) {
            $io->writeln(sprintf(
                '  - [ID: %d] %s (%d questions)',
                $quiz->getId(),
                $quiz->getTitle(),
                $quiz->getQuestions()->count()
            ));
        }
        
        // Test with filters
        $io->section('Testing with Filters');
        $filters = ['search' => 'test'];
        $queryBuilder = $this->quizRepository->findWithFiltersAndSort($filters, 'title', 'ASC');
        $pagination = $this->paginator->paginate($queryBuilder, 1, 5);
        
        $io->writeln(sprintf('Found %d quizzes matching "test"', $pagination->getTotalItemCount()));
        
        $io->success('Pagination test completed successfully!');
        
        return Command::SUCCESS;
    }
}
