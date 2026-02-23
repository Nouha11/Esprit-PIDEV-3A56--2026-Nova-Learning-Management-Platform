# Quiz AI Hint System

## Overview
The AI Hint System allows students to request intelligent hints for quiz questions using Google's Gemini API. Using a hint reduces the XP reward by 50%, encouraging students to try answering on their own first.

## Features

### 1. AI-Powered Hints
- Uses Google Gemini Pro to generate contextual hints
- Hints are tailored to the question difficulty level
- Provides guidance without revealing the answer directly
- Considers all answer choices when generating hints

### 2. XP Penalty System
- Using a hint reduces XP reward by 50%
- Original XP is shown with strikethrough
- Reduced XP is clearly displayed
- Penalty is applied when answer is submitted

### 3. One Hint Per Question
- Students can only request one hint per question
- Hint button disappears after use
- Hint is preserved if page is refreshed
- Session tracks hint usage per question

### 4. Fallback System
- If AI fails, provides difficulty-based fallback hints
- Ensures students always get help
- Logs errors for debugging
- Graceful degradation

## How It Works

### User Flow
1. Student sees a quiz question
2. Clicks "Get AI Hint (-50% XP)" button
3. System generates hint using OpenAI API
4. Hint is displayed with warning about XP reduction
5. XP badge updates to show reduced amount
6. Student answers question with hint assistance
7. Reduced XP is awarded if answer is correct

### Technical Flow
```
User clicks hint button
    ↓
AJAX request to /game/quiz/hint/{questionId}
    ↓
QuizHintService generates hint via OpenAI
    ↓
Session stores: hint_used_{questionId} = true
    ↓
Returns: hint text, original XP, reduced XP
    ↓
Frontend updates UI with hint and XP change
    ↓
User submits answer
    ↓
Controller checks hint_used flag
    ↓
Awards reduced XP if hint was used
```

## Implementation Details

### Service: QuizHintService
**Location:** `src/Service/Quiz/QuizHintService.php`

**Methods:**
- `generateHint(Question $question): string` - Generates AI hint
- `calculateXpWithHintPenalty(int $originalXp): int` - Calculates 50% XP
- `buildPrompt(Question $question): string` - Creates OpenAI prompt
- `getFallbackHint(Question $question): string` - Provides fallback

**Configuration:**
```yaml
# config/services.yaml
App\Service\Quiz\QuizHintService:
    arguments:
        $geminiApiKey: '%env(GEMINI_API_KEY)%'
```

### Controller: QuizGameController
**Location:** `src/Controller/Front/Quiz/QuizGameController.php`

**New Route:**
```php
#[Route('/hint/{questionId}', name: 'app_quiz_hint', methods: ['POST'])]
public function getHint(int $questionId, ...): JsonResponse
```

**Modified Routes:**
- `app_quiz_play` - Now checks for hint usage and applies penalty

### Template: play.html.twig
**Location:** `templates/front/quiz/game/play.html.twig`

**New Elements:**
- Hint button (shown if hint not used)
- Hint container (displays AI-generated hint)
- XP badge (updates to show reduced XP)
- JavaScript for AJAX hint request

## Session Variables

The system uses these session keys:
- `hint_used_{questionId}` - Boolean, tracks if hint was used
- `hint_text_{questionId}` - String, stores generated hint
- `quiz_score` - Integer, accumulates XP (with penalties applied)

## API Integration

### Google Gemini Configuration
```php
// Request to Gemini API
POST https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={API_KEY}
{
    "contents": [
        {
            "parts": [
                {
                    "text": "You are a helpful tutor... [full prompt]"
                }
            ]
        }
    ],
    "generationConfig": {
        "temperature": 0.7,
        "maxOutputTokens": 150,
        "topP": 0.8,
        "topK": 40
    }
}
```

### Response Format
```json
{
    "success": true,
    "hint": "Think about the basic principles...",
    "originalXp": 100,
    "reducedXp": 50,
    "penalty": 50
}
```

## Example Hints

### Easy Question
**Question:** "What is 2 + 2?"
**AI Hint:** "Think about basic addition. Count on your fingers if needed. The answer is a small, even number."

### Medium Question
**Question:** "What is the capital of France?"
**AI Hint:** "This city is known for the Eiffel Tower and is located in Western Europe. It's one of the most visited cities in the world."

### Hard Question
**Question:** "What is the time complexity of binary search?"
**AI Hint:** "Consider how the search space is divided in each step. Think about logarithmic operations and how quickly the problem size reduces."

## Fallback Hints

If AI fails, these difficulty-based hints are provided:

- **Easy:** "Think carefully about the basics. The answer is often simpler than you think!"
- **Medium:** "Consider what you know about this topic. Try to eliminate obviously wrong answers first."
- **Hard:** "This is a challenging question. Break it down into smaller parts and think about each component."

## UI/UX Features

### Visual Feedback
- Loading spinner while generating hint
- XP badge updates with strikethrough on original value
- Alert notification about XP reduction
- Hint displayed in info-styled alert box
- Button disabled after use

### Accessibility
- Clear labeling of XP penalty
- Visual indicators for hint usage
- Screen reader friendly
- Keyboard accessible

## Security Considerations

1. **Session Validation:** Checks if question is current before providing hint
2. **One-Time Use:** Prevents multiple hints for same question
3. **API Key Protection:** OpenAI key stored in environment variables
4. **Error Handling:** Graceful fallback if API fails
5. **Rate Limiting:** Consider adding rate limits in production

## Performance

### API Response Time
- Average: 2-3 seconds
- Timeout: 10 seconds
- Fallback: Instant (if API fails)

### Caching
Currently no caching implemented. Future enhancement:
- Cache hints per question
- Reduce API calls
- Faster response times

## Cost Considerations

### Google Gemini API Costs
- Model: Gemini Pro
- Free tier: 60 requests per minute
- Paid tier: Pay-as-you-go pricing

**Pricing (as of 2024):**
- Free tier available for development
- Production pricing varies by usage
- Generally more cost-effective than OpenAI

**Monthly Estimates (if using paid tier):**
- 1,000 hints/month = ~$1.00
- 10,000 hints/month = ~$10.00
- 100,000 hints/month = ~$100.00

Note: Gemini offers generous free tier for testing and development.

## Testing

### Manual Testing
1. Start a quiz
2. Click "Get AI Hint" button
3. Verify hint is displayed
4. Check XP badge shows reduced amount
5. Answer question correctly
6. Verify reduced XP is awarded

### Test Command
```bash
# Test hint generation
php bin/console app:test-quiz-hint
```

## Error Handling

### Common Errors
1. **API Key Invalid:** Returns fallback hint
2. **API Timeout:** Returns fallback hint after 10s
3. **Question Not Found:** Returns 404 error
4. **Hint Already Used:** Returns 400 error
5. **Invalid Question:** Returns 400 error

### Logging
All errors are logged to `var/log/dev.log`:
```
[error] Failed to generate quiz hint
    question_id: 123
    error: Connection timeout
```

## Future Enhancements

### Potential Improvements
1. **Multiple Hint Levels:** Progressive hints with increasing penalties
2. **Hint History:** Show hints used in quiz results
3. **Analytics:** Track hint usage patterns
4. **Custom Prompts:** Admin-defined hint strategies
5. **Hint Quality Rating:** Let students rate hint usefulness
6. **Gemini API Support:** Alternative AI provider
7. **Hint Caching:** Cache hints to reduce API costs
8. **Hint Preview:** Show hint quality before using

## Configuration

### Environment Variables
```env
# .env
GEMINI_API_KEY=your-gemini-api-key-here
```

### Service Configuration
```yaml
# config/services.yaml
parameters:
    gemini_api_key: '%env(GEMINI_API_KEY)%'

services:
    App\Service\Quiz\QuizHintService:
        arguments:
            $geminiApiKey: '%env(GEMINI_API_KEY)%'
```

## Troubleshooting

### Hint Button Not Working
- Check browser console for JavaScript errors
- Verify OpenAI API key is set in `.env`
- Check network tab for failed requests
- Clear browser cache

### Hints Not Generating
- Verify OpenAI API key is valid
- Check API quota/billing
- Review logs in `var/log/dev.log`
- Test with fallback hints

### XP Not Reducing
- Check session is working
- Verify hint_used flag is set
- Clear session and try again
- Check controller logic

### API Errors
- Check OpenAI API status
- Verify API key permissions
- Check network connectivity
- Review timeout settings

## Best Practices

### For Students
1. Try answering without hint first
2. Use hints for learning, not just points
3. Read hint carefully before answering
4. Understand why hint helps

### For Admins
1. Monitor API usage and costs
2. Review hint quality periodically
3. Adjust XP penalties if needed
4. Consider hint analytics

## Related Files

### Backend
- `src/Service/Quiz/QuizHintService.php` - Hint generation service
- `src/Controller/Front/Quiz/QuizGameController.php` - Hint endpoint
- `config/services.yaml` - Service configuration

### Frontend
- `templates/front/quiz/game/play.html.twig` - Hint UI
- JavaScript for AJAX requests

### Configuration
- `.env` - OpenAI API key
- `config/services.yaml` - Service bindings

## Conclusion

The AI Hint System provides intelligent, contextual assistance to students while maintaining the challenge and reward structure of the quiz system. The 50% XP penalty encourages independent thinking while still offering support when needed.
