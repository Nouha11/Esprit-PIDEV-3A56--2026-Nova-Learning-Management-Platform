<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TranslationService
{
    private HttpClientInterface $httpClient;
    private string $openaiApiKey;
    private array $cache = [];
    private FallbackTranslationService $fallbackService;

    public function __construct(
        HttpClientInterface $httpClient, 
        ParameterBagInterface $params,
        FallbackTranslationService $fallbackService
    ) {
        $this->httpClient = $httpClient;
        $this->openaiApiKey = $params->get('openai_api_key');
        $this->fallbackService = $fallbackService;
    }

    public function translateText(string $text, string $targetLanguage = 'fr'): string
    {
        // Try fallback translation first for common phrases
        if ($this->fallbackService->hasTranslation($text)) {
            return $this->fallbackService->translate($text);
        }

        // Return original text if API key is not configured
        if (empty($this->openaiApiKey) || $this->openaiApiKey === 'your_openai_api_key_here') {
            return $text;
        }

        // Check cache first
        $cacheKey = md5($text . $targetLanguage);
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        try {
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openaiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a professional translator. Translate the given text to ' . 
                                       ($targetLanguage === 'fr' ? 'French' : 'English') . 
                                       '. Only return the translated text, nothing else. Maintain the same tone and context.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $text
                        ]
                    ],
                    'max_tokens' => 1000,
                    'temperature' => 0.3
                ],
                'timeout' => 10
            ]);

            $data = $response->toArray();
            
            if (isset($data['choices'][0]['message']['content'])) {
                $translatedText = trim($data['choices'][0]['message']['content']);
                $this->cache[$cacheKey] = $translatedText;
                return $translatedText;
            }
            
            throw new \Exception('Invalid response from OpenAI API');
            
        } catch (\Exception $e) {
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
        return !empty($this->openaiApiKey) && $this->openaiApiKey !== 'your_openai_api_key_here';
    }
}