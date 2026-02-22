<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class TranslationService
{
    private const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
    
    private HttpClientInterface $httpClient;
    private string $geminiApiKey;
    private array $cache = [];
    private FallbackTranslationService $fallbackService;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $httpClient, 
        ParameterBagInterface $params,
        FallbackTranslationService $fallbackService,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->geminiApiKey = $params->get('gemini_api_key');
        $this->fallbackService = $fallbackService;
        $this->logger = $logger;
    }

    public function translateText(string $text, string $targetLanguage = 'fr'): string
    {
        // Try fallback translation first for common phrases
        if ($this->fallbackService->hasTranslation($text)) {
            return $this->fallbackService->translate($text);
        }

        // Return original text if API key is not configured
        if (empty($this->geminiApiKey) || $this->geminiApiKey === 'your_gemini_api_key_here') {
            return $text;
        }

        // Check cache first
        $cacheKey = md5($text . $targetLanguage);
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        try {
            $languageName = $targetLanguage === 'fr' ? 'French' : 'English';
            
            $prompt = "Translate the following text to {$languageName}. " .
                     "Only return the translated text, nothing else. " .
                     "Maintain the same tone, context, and formatting.\n\n" .
                     "Text to translate: {$text}";

            $response = $this->httpClient->request('POST', self::GEMINI_API_URL, [
                'query' => ['key' => $this->geminiApiKey],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'maxOutputTokens' => 1000,
                    ]
                ],
                'timeout' => 10
            ]);

            $data = $response->toArray();
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $translatedText = trim($data['candidates'][0]['content']['parts'][0]['text']);
                
                // Remove any markdown formatting that Gemini might add
                $translatedText = preg_replace('/^```.*\n/', '', $translatedText);
                $translatedText = preg_replace('/\n```$/', '', $translatedText);
                $translatedText = trim($translatedText);
                
                $this->cache[$cacheKey] = $translatedText;
                return $translatedText;
            }
            
            throw new \Exception('Invalid response from Gemini API');
            
        } catch (\Exception $e) {
            $this->logger->error('Translation failed: ' . $e->getMessage());
            // Fallback: return original text if translation fails
            return $text;
        }
    }

    public function translateArray(array $texts, string $targetLanguage = 'fr'): array
    {
        $translations = [];
        
        foreach ($texts as $key => $text) {
            if (is_string($text) && !empty(trim($text))) {
                $translations[$key] = $this->translateText($text, $targetLanguage);
            } else {
                $translations[$key] = $text;
            }
        }
        
        return $translations;
    }

    public function isConfigured(): bool
    {
        return !empty($this->geminiApiKey) && $this->geminiApiKey !== 'your_gemini_api_key_here';
    }
    
    /**
     * Batch translate multiple texts in one API call for better performance
     */
    public function batchTranslate(array $texts, string $targetLanguage = 'fr'): array
    {
        if (empty($texts)) {
            return [];
        }

        // Check fallback first
        $translations = [];
        $needsTranslation = [];
        
        foreach ($texts as $key => $text) {
            if ($this->fallbackService->hasTranslation($text)) {
                $translations[$key] = $this->fallbackService->translate($text);
            } else {
                $needsTranslation[$key] = $text;
            }
        }

        if (empty($needsTranslation)) {
            return $translations;
        }

        // Return original if API not configured
        if (!$this->isConfigured()) {
            return array_merge($translations, $needsTranslation);
        }

        try {
            $languageName = $targetLanguage === 'fr' ? 'French' : 'English';
            
            // Create numbered list for batch translation
            $textList = '';
            foreach ($needsTranslation as $key => $text) {
                $textList .= ($key + 1) . ". {$text}\n";
            }
            
            $prompt = "Translate the following texts to {$languageName}. " .
                     "Return only the translations in the same numbered format. " .
                     "Maintain the same tone and context.\n\n{$textList}";

            $response = $this->httpClient->request('POST', self::GEMINI_API_URL, [
                'query' => ['key' => $this->geminiApiKey],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'maxOutputTokens' => 2000,
                    ]
                ],
                'timeout' => 15
            ]);

            $data = $response->toArray();
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $result = trim($data['candidates'][0]['content']['parts'][0]['text']);
                
                // Parse numbered list
                $lines = explode("\n", $result);
                $index = 0;
                foreach ($needsTranslation as $key => $originalText) {
                    if (isset($lines[$index])) {
                        // Remove number prefix (e.g., "1. " or "1) ")
                        $translated = preg_replace('/^\d+[\.\)]\s*/', '', $lines[$index]);
                        $translations[$key] = trim($translated);
                    } else {
                        $translations[$key] = $originalText;
                    }
                    $index++;
                }
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Batch translation failed: ' . $e->getMessage());
            // Fallback to original texts
            $translations = array_merge($translations, $needsTranslation);
        }

        return $translations;
    }
}