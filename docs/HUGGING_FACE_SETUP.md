# Hugging Face AI Integration Guide

## Overview
The system uses Hugging Face's Mistral-7B-Instruct model to automatically generate trivia questions for games. This feature helps admins quickly create educational quiz content.

## Setup Instructions

### 1. Get Your Hugging Face API Key

1. Go to [Hugging Face](https://huggingface.co/)
2. Sign up for a free account (if you don't have one)
3. Go to your [Settings → Access Tokens](https://huggingface.co/settings/tokens)
4. Click "New token"
5. Give it a name (e.g., "NOVA Quiz Generator")
6. Select "Read" permission (sufficient for inference API)
7. Click "Generate token"
8. Copy the token (starts with `hf_...`)

### 2. Add API Key to .env File

Open your `.env` file and add:

```env
###> Hugging Face API ###
HUGGING_FACE_API_KEY=hf_your_actual_token_here
###< Hugging Face API ###
```

Replace `hf_your_actual_token_here` with your actual token.

### 3. Clear Cache

```bash
php bin/console cache:clear
```

## How It Works

### Service: HuggingFaceService

**Location**: `src/Service/AI/HuggingFaceService.php`

**Main Method**: `generateTriviaQuestions(string $topic, int $count = 5)`

**What it does**:
1. Sends a prompt to Mistral-7B-Instruct model
2. Asks for multiple choice questions about a specific topic
3. Parses the AI response into structured question data
4. Returns an array of questions with choices and correct answers

### Example Usage in Controller

```php
use App\Service\AI\HuggingFaceService;

class GameAdminController extends AbstractController
{
    public function __construct(
        private HuggingFaceService $huggingFaceService
    ) {}
    
    #[Route('/admin/games/generate-questions', methods: ['POST'])]
    public function generateQuestions(Request $request): JsonResponse
    {
        $topic = $request->request->get('topic');
        $count = $request->request->getInt('count', 5);
        
        try {
            $questions = $this->huggingFaceService->generateTriviaQuestions($topic, $count);
            
            return $this->json([
                'success' => true,
                'questions' => $questions
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
```

### Question Format

The service returns questions in this format:

```php
[
    [
        'question' => 'What year did World War II end?',
        'choices' => ['1943', '1944', '1945', '1946'],
        'correct' => 2  // Index of correct answer (0-based)
    ],
    [
        'question' => 'Who was the first president of the United States?',
        'choices' => ['Thomas Jefferson', 'George Washington', 'John Adams', 'Benjamin Franklin'],
        'correct' => 1
    ],
    // ... more questions
]
```

## Integration with Game Creation

### Add to Game Form

In `templates/admin/game/new.html.twig` or `edit.html.twig`:

```html
<!-- Only show for TRIVIA games -->
<div id="ai-generator-section" style="display: none;">
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-robot"></i> AI Question Generator
            </h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="ai-topic" class="form-label">Topic</label>
                <input type="text" 
                       class="form-control" 
                       id="ai-topic" 
                       placeholder="e.g., World History, Mathematics, Science">
                <small class="text-muted">Enter a topic and AI will generate questions for you</small>
            </div>
            
            <div class="mb-3">
                <label for="ai-count" class="form-label">Number of Questions</label>
                <select class="form-select" id="ai-count">
                    <option value="3">3 questions</option>
                    <option value="5" selected>5 questions</option>
                    <option value="7">7 questions</option>
                    <option value="10">10 questions</option>
                </select>
            </div>
            
            <button type="button" 
                    class="btn btn-primary" 
                    id="generate-questions-btn">
                <i class="bi bi-magic"></i> Generate Questions with AI
            </button>
            
            <div id="ai-loading" class="mt-3" style="display: none;">
                <div class="spinner-border spinner-border-sm me-2"></div>
                Generating questions... This may take 10-30 seconds.
            </div>
            
            <div id="ai-error" class="alert alert-danger mt-3" style="display: none;"></div>
            <div id="ai-success" class="alert alert-success mt-3" style="display: none;"></div>
        </div>
    </div>
</div>

<script>
// Show AI generator only for TRIVIA games
document.getElementById('game_form_type').addEventListener('change', function() {
    const aiSection = document.getElementById('ai-generator-section');
    aiSection.style.display = this.value === 'TRIVIA' ? 'block' : 'none';
});

// Generate questions button
document.getElementById('generate-questions-btn').addEventListener('click', async function() {
    const topic = document.getElementById('ai-topic').value.trim();
    const count = document.getElementById('ai-count').value;
    
    if (!topic) {
        alert('Please enter a topic');
        return;
    }
    
    // Show loading
    document.getElementById('ai-loading').style.display = 'block';
    document.getElementById('ai-error').style.display = 'none';
    document.getElementById('ai-success').style.display = 'none';
    this.disabled = true;
    
    try {
        const response = await fetch('/admin/games/generate-questions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                topic: topic,
                count: count
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Pre-fill question fields with generated data
            fillQuestionFields(data.questions);
            
            document.getElementById('ai-success').textContent = 
                `Successfully generated ${data.questions.length} questions! Review and edit them below.`;
            document.getElementById('ai-success').style.display = 'block';
        } else {
            throw new Error(data.error || 'Failed to generate questions');
        }
    } catch (error) {
        document.getElementById('ai-error').textContent = error.message;
        document.getElementById('ai-error').style.display = 'block';
    } finally {
        document.getElementById('ai-loading').style.display = 'none';
        this.disabled = false;
    }
});

function fillQuestionFields(questions) {
    // This depends on your form structure
    // Example: if you have a collection of question fields
    questions.forEach((q, index) => {
        // Fill in question text
        const questionField = document.getElementById(`question_${index}_text`);
        if (questionField) {
            questionField.value = q.question;
        }
        
        // Fill in choices
        q.choices.forEach((choice, choiceIndex) => {
            const choiceField = document.getElementById(`question_${index}_choice_${choiceIndex}`);
            if (choiceField) {
                choiceField.value = choice;
            }
        });
        
        // Mark correct answer
        const correctField = document.getElementById(`question_${index}_correct`);
        if (correctField) {
            correctField.value = q.correct;
        }
    });
}
</script>
```

## API Limits & Costs

### Free Tier
- Hugging Face Inference API is **FREE** for public models
- Rate limits: ~1000 requests per day
- Response time: 5-30 seconds depending on model load

### Paid Tier (Inference Endpoints)
- Faster responses (< 1 second)
- Higher rate limits
- Dedicated resources
- Costs: ~$0.60/hour for small models

## Troubleshooting

### Error: "API key is not configured"
**Solution**: Add `HUGGING_FACE_API_KEY` to your `.env` file

### Error: "Model is loading"
**Solution**: Wait 20-30 seconds and try again. Models "cold start" when not used recently.

### Error: "Failed to parse generated questions"
**Solution**: The AI response format was unexpected. Try again or adjust the prompt in `HuggingFaceService::buildPrompt()`

### Error: "Rate limit exceeded"
**Solution**: You've hit the free tier limit. Wait an hour or upgrade to paid tier.

### Questions are low quality
**Solution**: 
- Be more specific with the topic (e.g., "Ancient Roman History" instead of "History")
- Adjust the temperature parameter in the service (lower = more focused)
- Review and edit generated questions before saving

## Testing the Connection

You can test if your API key works:

```php
$isConnected = $this->huggingFaceService->testConnection();

if ($isConnected) {
    echo "✓ Hugging Face API is working!";
} else {
    echo "✗ Connection failed. Check your API key.";
}
```

## Alternative Models

You can change the model by updating the `API_URL` constant in `HuggingFaceService.php`:

```php
// Current model (good for questions)
private const API_URL = 'https://api-inference.huggingface.co/models/mistralai/Mistral-7B-Instruct-v0.3';

// Alternative models:
// - Faster but less accurate
private const API_URL = 'https://api-inference.huggingface.co/models/google/flan-t5-large';

// - Better for creative content
private const API_URL = 'https://api-inference.huggingface.co/models/meta-llama/Llama-2-7b-chat-hf';
```

## Best Practices

1. **Always review AI-generated questions** before publishing
2. **Edit for clarity** - AI sometimes generates ambiguous questions
3. **Verify correct answers** - AI can make mistakes
4. **Use specific topics** - "World War II Timeline" works better than "History"
5. **Cache results** - Don't regenerate the same questions repeatedly
6. **Set reasonable timeouts** - AI can take 10-30 seconds to respond

## Example Topics That Work Well

✓ Good topics:
- "Ancient Egyptian Civilization"
- "Basic Algebra Equations"
- "Human Anatomy - Cardiovascular System"
- "JavaScript Programming Fundamentals"
- "World War II Major Battles"

✗ Too broad:
- "History"
- "Science"
- "Math"
- "Programming"

## Security Notes

- **Never commit API keys** to version control
- **Use environment variables** for all API keys
- **Rotate keys regularly** if exposed
- **Monitor usage** to detect unauthorized access
- **Validate AI output** before storing in database

## Support

- Hugging Face Documentation: https://huggingface.co/docs/api-inference
- Model Card: https://huggingface.co/mistralai/Mistral-7B-Instruct-v0.1
- Community Forum: https://discuss.huggingface.co/
