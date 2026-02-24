<?php

namespace App\Service\Library;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service pour l'assistant IA qui explique le texte sélectionné
 * Supporte plusieurs fournisseurs: OpenAI, DeepSeek, etc.
 */
class AiAssistantService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private string $provider;
    private string $apiUrl;
    private string $model;

    public function __construct(
        HttpClientInterface $httpClient, 
        string $aiApiKey,
        string $aiProvider = 'deepseek'
    ) {
        $this->httpClient = $httpClient;
        $this->apiKey = $aiApiKey;
        $this->provider = strtolower($aiProvider);
        
        // Configuration selon le fournisseur
        $this->configureProvider();
    }

    /**
     * Configure l'URL et le modèle selon le fournisseur
     */
    private function configureProvider(): void
    {
        switch ($this->provider) {
            case 'deepseek':
                $this->apiUrl = 'https://api.deepseek.com/v1/chat/completions';
                $this->model = 'deepseek-chat';
                break;
                
            case 'openai':
                $this->apiUrl = 'https://api.openai.com/v1/chat/completions';
                $this->model = 'gpt-3.5-turbo';
                break;
                
            case 'groq':
                $this->apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
                $this->model = 'llama-3.3-70b-versatile'; // Updated model
                break;
                
            default:
                // Par défaut, utiliser DeepSeek
                $this->apiUrl = 'https://api.deepseek.com/v1/chat/completions';
                $this->model = 'deepseek-chat';
        }
    }

    /**
     * Explique le texte sélectionné en utilisant l'API IA
     * 
     * @param string $selectedText Le texte sélectionné par l'utilisateur
     * @param string $context Contexte supplémentaire (titre du livre, etc.)
     * @param string $userQuestion Question spécifique de l'utilisateur (optionnel)
     * @return array Résultat avec l'explication
     */
    public function explainText(string $selectedText, string $context = '', string $userQuestion = ''): array
    {
        try {
            // Si pas de clé API, utiliser le mode fallback
            if (empty($this->apiKey) || $this->apiKey === 'your_api_key_here') {
                return $this->getFallbackExplanation($selectedText);
            }

            // Construire un prompt plus détaillé pour de vraies explications
            $systemPrompt = "You are a helpful reading tutor. Your job is to EXPLAIN concepts, not just rephrase. Break down complex ideas, define difficult terms, provide examples, and help the reader truly understand. Be clear, educational, and insightful.";
            
            $userPrompt = "I'm reading this text and need help understanding it:\n\n\"$selectedText\"\n\n";
            
            if ($userQuestion) {
                $userPrompt .= "Specifically, I don't understand: $userQuestion\n\n";
                $userPrompt .= "Please explain this part in detail, breaking down the concepts and providing examples if helpful.";
            } else {
                $userPrompt .= "Please:\n";
                $userPrompt .= "1. Explain the main concept or idea in simple terms\n";
                $userPrompt .= "2. Define any technical or difficult terms\n";
                $userPrompt .= "3. Provide context or examples to help me understand\n";
                $userPrompt .= "4. Clarify why this is important or how it connects to the bigger picture\n\n";
                $userPrompt .= "Don't just rephrase - actually teach me what this means!";
            }
            
            if ($context) {
                $userPrompt .= "\n\n(Context: This is from the book \"$context\")";
            }

            // Appel à l'API avec timeout
            $response = $this->httpClient->request('POST', $this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt
                        ],
                        [
                            'role' => 'user',
                            'content' => $userPrompt
                        ]
                    ],
                    'max_tokens' => 400,
                    'temperature' => 0.7,
                ],
                'timeout' => 20
            ]);

            $data = $response->toArray();
            
            return [
                'success' => true,
                'explanation' => $data['choices'][0]['message']['content'] ?? 'No explanation available.',
                'provider' => $this->provider
            ];

        } catch (\Symfony\Contracts\HttpClient\Exception\ClientException $e) {
            // Handle rate limiting (429) and other client errors
            $statusCode = $e->getResponse()->getStatusCode();
            
            if ($statusCode === 429) {
                return [
                    'success' => false,
                    'error' => 'Rate limit exceeded. Using fallback mode.',
                    'explanation' => $this->getSmartFallbackExplanation($selectedText)
                ];
            }
            
            return [
                'success' => false,
                'error' => 'API Error: ' . $statusCode,
                'explanation' => $this->getSmartFallbackExplanation($selectedText)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'explanation' => $this->getSmartFallbackExplanation($selectedText)
            ];
        }
    }

    /**
     * Fournit une explication intelligente sans API
     */
    private function getSmartFallbackExplanation(string $selectedText): string
    {
        $wordCount = str_word_count($selectedText);
        $sentences = preg_split('/[.!?]+/', $selectedText, -1, PREG_SPLIT_NO_EMPTY);
        $sentenceCount = count($sentences);
        
        // Détecter la langue
        $isFrench = preg_match('/\b(le|la|les|un|une|des|et|dans|pour|avec|sur|ce|cette|qui|que)\b/i', $selectedText);
        
        $explanation = "📚 **Text Analysis**\n\n";
        $explanation .= "• **Length**: $wordCount words, $sentenceCount sentence(s)\n";
        $explanation .= "• **Language**: " . ($isFrench ? "French" : "English/Other") . "\n\n";
        
        // Extraire les mots clés (mots de plus de 5 lettres)
        $words = str_word_count(strtolower($selectedText), 1);
        $keywords = array_filter($words, function($word) {
            return strlen($word) > 5;
        });
        $keywords = array_unique($keywords);
        $keywords = array_slice($keywords, 0, 5);
        
        if (!empty($keywords)) {
            $explanation .= "• **Key terms**: " . implode(', ', $keywords) . "\n\n";
        }
        
        $explanation .= "💡 **Quick Summary**:\n";
        if (!empty($sentences)) {
            $firstSentence = trim($sentences[0]);
            $explanation .= "This passage discusses: " . strtolower(substr($firstSentence, 0, 100)) . (strlen($firstSentence) > 100 ? '...' : '') . "\n\n";
        }
        
        $explanation .= "ℹ️ **Note**: Using local text analysis. Configure an AI API key for deeper insights.";
        
        return $explanation;
    }

    /**
     * Fournit une explication de secours quand l'API n'est pas disponible
     */
    private function getFallbackExplanation(string $selectedText): array
    {
        return [
            'success' => true,
            'explanation' => $this->getSmartFallbackExplanation($selectedText),
            'fallback' => true
        ];
    }

    /**
     * Traduit le texte sélectionné
     */
    public function translateText(string $text, string $targetLanguage = 'fr'): array
    {
        try {
            if (empty($this->apiKey) || $this->apiKey === 'your_api_key_here') {
                return [
                    'success' => false,
                    'error' => 'API key not configured'
                ];
            }

            $response = $this->httpClient->request('POST', $this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => "Translate the following text to $targetLanguage. Only provide the translation, no explanations:\n\n$text"
                        ]
                    ],
                    'max_tokens' => 500,
                    'temperature' => 0.3,
                ],
                'timeout' => 15
            ]);

            $data = $response->toArray();
            
            return [
                'success' => true,
                'translation' => $data['choices'][0]['message']['content'] ?? 'Translation not available.'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
