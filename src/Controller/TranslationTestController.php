<?php

namespace App\Controller;

use App\Service\TranslationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TranslationTestController extends AbstractController
{
    #[Route('/test-translation', name: 'app_test_translation')]
    public function testTranslation(TranslationService $translationService, Request $request): JsonResponse
    {
        $text = $request->query->get('text', 'Student Profile');
        $targetLang = $request->query->get('lang', 'fr');
        
        try {
            $translatedText = $translationService->translateText($text, $targetLang);
            
            return new JsonResponse([
                'success' => true,
                'original' => $text,
                'translated' => $translatedText,
                'target_language' => $targetLang,
                'api_configured' => $translationService->isConfigured(),
                'using_fallback' => !$translationService->isConfigured()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
                'original' => $text,
                'api_configured' => $translationService->isConfigured()
            ]);
        }
    }
}