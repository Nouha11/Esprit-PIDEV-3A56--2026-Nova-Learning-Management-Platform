<?php

namespace App\Controller\Front\Library;

use App\Service\Library\AiAssistantService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur pour l'assistant IA de lecture
 */
class AiAssistantController extends AbstractController
{
    #[Route('/api/ai/explain', name: 'ai_explain_text', methods: ['POST'])]
    public function explainText(Request $request, AiAssistantService $aiService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $selectedText = $data['text'] ?? '';
        $bookTitle = $data['bookTitle'] ?? '';
        $userQuestion = $data['question'] ?? '';
        
        if (empty($selectedText)) {
            return $this->json([
                'success' => false,
                'error' => 'No text provided'
            ], 400);
        }

        // Limiter la longueur du texte (max 500 caractères)
        if (strlen($selectedText) > 500) {
            $selectedText = substr($selectedText, 0, 500) . '...';
        }

        $result = $aiService->explainText($selectedText, $bookTitle, $userQuestion);
        
        return $this->json($result);
    }

    #[Route('/api/ai/translate', name: 'ai_translate_text', methods: ['POST'])]
    public function translateText(Request $request, AiAssistantService $aiService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $selectedText = $data['text'] ?? '';
        $targetLanguage = $data['language'] ?? 'fr';
        
        if (empty($selectedText)) {
            return $this->json([
                'success' => false,
                'error' => 'No text provided'
            ], 400);
        }

        // Limiter la longueur du texte
        if (strlen($selectedText) > 500) {
            $selectedText = substr($selectedText, 0, 500) . '...';
        }

        $result = $aiService->translateText($selectedText, $targetLanguage);
        
        return $this->json($result);
    }
}
