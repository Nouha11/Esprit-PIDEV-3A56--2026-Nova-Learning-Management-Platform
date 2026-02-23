<?php

namespace App\Command\Quiz;

use App\Repository\Quiz\QuestionRepository;
use App\Service\Quiz\QuizHintService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-quiz-hint',
    description: 'Test the AI hint generation system for quiz questions'
)]
class TestQuizHintCommand extends Command
{
    public function __construct(
        private QuestionRepository $questionRepository,
        private QuizHintService $hintService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('question-id', null, InputOption::VALUE_OPTIONAL, 'Specific question ID to test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Quiz AI Hint System Test');
        
        $questionId = $input->getOption('question-id');
        
        if ($questionId) {
            $question = $this->questionRepository->find($questionId);
            if (!$question) {
                $io->error("Question with ID {$questionId} not found.");
                return Command::FAILURE;
            }
            $questions = [$question];
        } else {
            $questions = $this->questionRepository->findAll();
            if (empty($questions)) {
                $io->warning('No questions found in the database.');
                return Command::SUCCESS;
            }
            // Test with first 3 questions
            $questions = array_slice($questions, 0, 3);
        }
        
        $io->section('Testing Hint Generation');
        
        foreach ($questions as $question) {
            $io->writeln('');
            $io->writeln('<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
            $io->writeln(sprintf('<fg=yellow>Question #%d</> [%s]', $question->getId(), $question->getDifficulty()));
            $io->writeln('<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
            $io->writeln('');
            
            $io->writeln('<fg=white;options=bold>Question:</>');
            $io->writeln($question->getText());
            $io->writeln('');
            
            $io->writeln('<fg=white;options=bold>Choices:</>');
            foreach ($question->getChoices() as $index => $choice) {
                $marker = $choice->isCorrect() ? '✓' : ' ';
                $io->writeln(sprintf('  [%s] %s', $marker, $choice->getContent()));
            }
            $io->writeln('');
            
            $io->writeln('<fg=white;options=bold>Original XP:</> ' . $question->getXpValue());
            $reducedXp = $this->hintService->calculateXpWithHintPenalty($question->getXpValue());
            $io->writeln('<fg=white;options=bold>XP with Hint:</> ' . $reducedXp . ' (-50%)');
            $io->writeln('');
            
            $io->write('<fg=white;options=bold>Generating AI Hint...</> ');
            
            try {
                $startTime = microtime(true);
                $hint = $this->hintService->generateHint($question);
                $duration = round((microtime(true) - $startTime) * 1000);
                
                $io->writeln(sprintf('<fg=green>✓</> (took %dms)', $duration));
                $io->writeln('');
                $io->writeln('<fg=white;bg=blue;options=bold> AI HINT </>');
                $io->writeln('<fg=blue>' . $hint . '</>');
                
            } catch (\Exception $e) {
                $io->writeln('<fg=red>✗ Failed</>');
                $io->error('Error: ' . $e->getMessage());
            }
            
            $io->writeln('');
        }
        
        $io->success('Hint generation test completed!');
        
        $io->section('Summary');
        $io->table(
            ['Metric', 'Value'],
            [
                ['Questions Tested', count($questions)],
                ['XP Penalty', '50%'],
                ['AI Model', 'Google Gemini 2.5 Flash'],
                ['Max Tokens', '150'],
            ]
        );
        
        return Command::SUCCESS;
    }
}
