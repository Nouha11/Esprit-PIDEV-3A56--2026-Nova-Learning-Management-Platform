<?php

namespace App\Controller\Admin\Game;

use App\Service\game\HuggingFaceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/games/ai')]
#[IsGranted('ROLE_ADMIN')]
class AIQuestionGeneratorController extends AbstractController
{
    public function __construct(
        private HuggingFaceService $huggingFaceService
    ) {
    }

    /**
     * Generate trivia questions using AI
     */
    #[Route('/generate-questions', name: 'admin_game_ai_generate_questions', methods: ['POST'])]
    public function generateQuestions(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $topic = $data['topic'] ?? '';
        $count = (int)($data['count'] ?? 5);

        // Validate input
        if (empty($topic)) {
            return $this->json([
                'success' => false,
                'message' => 'Topic is required'
            ], 400);
        }

        if ($count < 3 || $count > 10) {
            return $this->json([
                'success' => false,
                'message' => 'Question count must be between 3 and 10'
            ], 400);
        }

        try {
            $questions = $this->huggingFaceService->generateTriviaQuestions($topic, $count);

            if (empty($questions)) {
                return $this->json([
                    'success' => false,
                    'message' => 'No questions were generated. Please try again with a different topic.'
                ], 500);
            }

            return $this->json([
                'success' => true,
                'message' => sprintf('Successfully generated %d questions about "%s"', count($questions), $topic),
                'questions' => $questions,
                'count' => count($questions)
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to generate questions: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test AI API connection
     */
    #[Route('/test-connection', name: 'admin_game_ai_test_connection', methods: ['GET'])]
    public function testConnection(): JsonResponse
    {
        try {
            $isConnected = $this->huggingFaceService->testConnection();

            if ($isConnected) {
                return $this->json([
                    'success' => true,
                    'message' => 'Successfully connected to Hugging Face API'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Failed to connect to Hugging Face API. Please check your API key.'
                ], 500);
            }
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
