<?php

namespace App\Service\Forum;

use App\Entity\Forum\Post;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AiSummaryService
{
    public function __construct(
        private HttpClientInterface $client,
        // This magic attribute automatically grabs the key from your .env file!
        #[Autowire(env: 'GEMINI_API_KEY')] private string $apiKey 
    ) {
    }

    public function generateSummary(Post $post): string
    {
        // 1. Gather all the text from the discussion
        $discussionText = "Title: " . $post->getTitle() . "\n";
        $discussionText .= "Original Post: " . $post->getContent() . "\n\n";
        $discussionText .= "Replies:\n";

        foreach ($post->getComments() as $comment) {
            $discussionText .= "- " . $comment->getContent() . "\n";
        }

        // 2. Tell the AI exactly what we want it to do
        $prompt = "You are a helpful forum assistant. Please read the following discussion and provide a very concise summary (maximum 3 sentences) of the main problem and any solutions discussed:\n\n" . $discussionText;

       // 3. Send the request to Google Gemini API
        try {
            $response = $this->client->request(
                'POST',
                // 👇 WE UPGRADED THE MODEL TO gemini-2.5-flash HERE 👇
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $this->apiKey,
                [
                    // --- THE XAMPP FIX: Ignore SSL verification locally ---
                    'verify_peer' => false,
                    'verify_host' => false,
                    // ------------------------------------------------------
                    'json' => [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ]
                    ]
                ]
            );

            // 4. Decode the JSON response and return just the text
            $data = $response->toArray();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No summary could be generated.';
            
        } catch (\Exception $e) {
            // INSTEAD OF NAPPING, SHOW THE ACTUAL ERROR:
            return 'AI Error: ' . $e->getMessage();
        }
    }
}