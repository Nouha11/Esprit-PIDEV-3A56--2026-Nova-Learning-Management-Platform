<?php

namespace App\Controller\Front\AI;

use App\Service\game\AIRewardRecommendationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ai-assistant')]
#[IsGranted('ROLE_STUDENT')]
class AIAssistantController extends AbstractController
{
    public function __construct(
        private AIRewardRecommendationService $aiService
    ) {
    }

    /**
     * Get AI reward recommendation
     */
    #[Route('/recommendation', name: 'ai_assistant_recommendation', methods: ['GET'])]
    public function getRecommendation(): JsonResponse
    {
        $user = $this->getUser();
        $student = $user->getStudentProfile();
        
        if (!$student) {
            return $this->json([
                'success' => false,
                'message' => 'Student profile not found.'
            ], 404);
        }

        $result = $this->aiService->generateRecommendation($student);
        
        return $this->json($result);
    }

    /**
     * Chat with AI assistant
     */
    #[Route('/chat', name: 'ai_assistant_chat', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $student = $user->getStudentProfile();
        
        if (!$student) {
            return $this->json([
                'success' => false,
                'message' => 'Student profile not found.'
            ], 404);
        }

        $data = json_decode($request->getContent(), true);
        $question = $data['question'] ?? '';
        
        if (empty(trim($question))) {
            return $this->json([
                'success' => false,
                'message' => 'Please enter a question.'
            ], 400);
        }

        $result = $this->aiService->generateChatResponse($student, $question);
        
        return $this->json($result);
    }
}
