# AI Reward Assistant & Chat Widget

## Overview

An AI-powered reward recommendation system with an interactive chat widget that helps students discover rewards, track progress, and get personalized guidance throughout the gamification platform.

## Features

### 1. Personalized Reward Recommendations
- AI analyzes student's current progress (XP, level, tokens)
- Evaluates unlocked vs. available rewards
- Suggests the most achievable next reward
- Provides motivational guidance on how to unlock it

### 2. Interactive Chat Assistant
- Real-time conversational AI interface
- Context-aware responses based on student data
- Answers questions about progress, rewards, and games
- Friendly and encouraging tone

### 3. Quick Actions
- One-click reward recommendations
- Progress tracking queries
- Common question shortcuts

### 4. Modern UI/UX
- Floating chat button with pulse animation
- Slide-up chat window
- Typing indicators for better feedback
- Fully responsive design
- Dark/light theme compatible
- Smooth animations and transitions

## Technical Implementation

### Backend Components

#### 1. AI Service (`src/Service/AI/AIRewardRecommendationService.php`)

**Key Methods:**
- `generateRecommendation(StudentProfile $student)` - Creates personalized reward suggestions
- `generateChatResponse(StudentProfile $student, string $question)` - Handles chat conversations
- `collectStudentData(StudentProfile $student)` - Gathers context for AI prompts

**Data Collection:**
```php
[
    'level' => $student->getLevel(),
    'xp' => $student->getTotalXP(),
    'tokens' => $student->getTotalTokens(),
    'unlockedCount' => count($unlockedRewards),
    'unlockedRewards' => [...], // Recent unlocked rewards
    'availableRewards' => [...] // Available rewards with requirements
]
```

**API Configuration:**
- Endpoint: `https://router.huggingface.co/novita/v3/openai/chat/completions`
- Model: `qwen/qwen2.5-7b-instruct`
- Temperature: 0.7-0.8 (for natural, varied responses)
- Max Tokens: 200-250

#### 2. Controller (`src/Controller/Front/AI/AIAssistantController.php`)

**Routes:**
- `GET /ai-assistant/recommendation` - Get AI reward recommendation
- `POST /ai-assistant/chat` - Send chat message and get response

**Security:**
- Requires `ROLE_STUDENT` authentication
- Validates student profile exists
- Input sanitization for chat messages

### Frontend Components

#### 1. Chat Widget (`templates/components/ai_chat_widget.html.twig`)

**Structure:**
```
ai-chat-widget/
├── ai-chat-toggle (floating button)
├── ai-chat-window
│   ├── ai-chat-header
│   ├── ai-chat-messages
│   │   ├── ai-message (AI responses)
│   │   └── user-message (user inputs)
│   └── ai-chat-input
│       ├── ai-quick-actions (shortcut buttons)
│       └── input-group (text input + send button)
```

**Styling Features:**
- Gradient purple theme (`#667eea` to `#764ba2`)
- Pulse animation on toggle button
- Slide-up animation for chat window
- Typing indicator with animated dots
- Message bubbles with avatars
- Responsive breakpoints for mobile

**JavaScript Functionality:**
- Toggle chat window visibility
- Send messages via AJAX
- Display typing indicators
- Auto-scroll to latest message
- Quick action button handlers
- Enter key support for sending messages

## Integration Points

The chat widget is integrated into the following pages:

1. **Student Dashboard** (`/`)
   - File: `templates/front/users/student/dashboard.html.twig`
   - Context: Main student hub

2. **Games Index** (`/games`)
   - File: `templates/front/game/index.html.twig`
   - Context: Browse all games

3. **Browse Rewards** (`/rewards/browse`)
   - File: `templates/front/game/browse.html.twig`
   - Context: Explore available rewards

4. **My Rewards** (`/rewards/my-rewards`)
   - File: `templates/front/game/my_rewards.html.twig`
   - Context: View unlocked rewards

5. **My Favorites** (`/games/favorites/my-favorites`)
   - File: `templates/front/game/favorites.html.twig`
   - Context: Favorite games collection

## Usage Examples

### Getting a Reward Recommendation

**User Action:** Click "Recommend Reward" quick action button

**AI Response Example:**
```
Great progress! You're at Level 5 with 450 XP and 120 tokens. 
I recommend aiming for the "Bronze Champion" badge next - you 
need to reach Level 6 (just 50 more XP!). Keep playing games 
to level up quickly!
```

### Asking About Progress

**User Input:** "How am I doing?"

**AI Response Example:**
```
You're doing fantastic! You're currently at Level 5 with 450 XP 
and 120 tokens. You've unlocked 3 rewards so far. To reach the 
next level, you need 50 more XP. Keep up the great work!
```

### Asking About Specific Rewards

**User Input:** "What rewards can I unlock soon?"

**AI Response Example:**
```
Based on your current progress, you're close to unlocking:
- Bronze Champion badge (need Level 6)
- 100 Token Bonus (need 500 total XP)
- Speed Demon achievement (complete 5 arcade games)

The easiest one to get is the Bronze Champion - just 50 more XP!
```

## AI Prompt Engineering

### Recommendation Prompt Structure

```
You are a motivational gaming coach helping a student in a gamification system.

Student Status:
- Level: {level}
- XP: {xp}
- Tokens: {tokens}
- Unlocked Rewards: {list}

Available Rewards to Unlock:
{reward_list_with_requirements}

Task: Write a short, motivational message (2-3 sentences) recommending 
which reward the student should aim for next and what they need to do 
to unlock it. Be encouraging and specific.
```

### Chat Prompt Structure

```
You are a helpful gaming assistant in a gamification system. 
Answer the student's question based on their current status.

Student Status:
- Level: {level}
- XP: {xp}
- Tokens: {tokens}
- Unlocked Rewards: {count}

Available Rewards:
{reward_list}

Student Question: {question}

Provide a helpful, concise answer (2-4 sentences). 
Be friendly and encouraging.
```

## Configuration

### Environment Variables

Required in `.env`:
```bash
HUGGING_FACE_API_KEY=your_api_key_here
```

### Service Registration

The service is auto-wired in Symfony. Ensure `services.yaml` includes:

```yaml
services:
    App\Service\AI\AIRewardRecommendationService:
        arguments:
            $huggingFaceApiKey: '%env(HUGGING_FACE_API_KEY)%'
```

## Error Handling

### API Failures
- Graceful fallback messages
- Logged errors for debugging
- User-friendly error messages

### Missing Data
- Checks for student profile existence
- Validates reward data availability
- Handles empty reward lists

### Network Issues
- 15-second timeout on API calls
- Retry logic not implemented (single attempt)
- Clear error messages to users

## Performance Considerations

### Response Times
- API calls: ~2-5 seconds average
- Typing indicator provides visual feedback
- Async AJAX prevents page blocking

### Data Optimization
- Limits unlocked rewards to 5 most recent
- Limits available rewards to 10 most relevant
- Reduces prompt size for faster responses

### Caching
- No caching implemented (real-time data)
- Consider caching for frequently asked questions
- Student data always fresh

## Styling & Theming

### Color Scheme
- Primary: `#667eea` (purple)
- Secondary: `#764ba2` (darker purple)
- Success: `#28a745` (green for user messages)
- Background: White (light) / `#1a1d29` (dark)

### Animations
- `pulse`: 2s infinite (toggle button ring)
- `slideUp`: 0.3s ease (window appearance)
- `fadeIn`: 0.3s ease (messages)
- `typing`: 1.4s infinite (typing indicator)

### Responsive Design
- Desktop: 380px width, 500px height
- Mobile: Full width minus 40px padding
- Mobile height: 450px

## Future Enhancements

### Potential Features
1. **Conversation History**
   - Store chat history in session/database
   - Allow users to review past conversations
   - Context continuity across sessions

2. **Advanced Recommendations**
   - Machine learning for personalized suggestions
   - Analyze play patterns and preferences
   - Predict which rewards users will enjoy

3. **Multi-language Support**
   - Detect user language preference
   - Translate AI responses
   - Localized prompts

4. **Voice Input**
   - Speech-to-text integration
   - Voice responses (text-to-speech)
   - Accessibility improvements

5. **Gamification of Chat**
   - Earn XP for asking questions
   - Unlock chat themes/avatars
   - Daily chat challenges

6. **Analytics Dashboard**
   - Track most asked questions
   - Monitor AI response quality
   - Identify common user needs

## Troubleshooting

### Chat Widget Not Appearing
1. Check if user has `ROLE_STUDENT`
2. Verify template includes widget component
3. Check browser console for JavaScript errors
4. Ensure CSS is loading properly

### AI Not Responding
1. Verify `HUGGING_FACE_API_KEY` is set
2. Check API endpoint is accessible
3. Review logs for error messages
4. Test API connection manually

### Slow Responses
1. Check network latency
2. Verify API rate limits not exceeded
3. Consider reducing prompt size
4. Monitor server resources

### Incorrect Recommendations
1. Verify student data is accurate
2. Check reward requirements are correct
3. Review AI prompt structure
3. Adjust temperature parameter

## Testing

### Manual Testing Checklist
- [ ] Chat widget appears on all 5 pages
- [ ] Toggle button opens/closes chat window
- [ ] Quick action buttons work
- [ ] Text input and send button work
- [ ] Enter key sends messages
- [ ] Typing indicator displays during API calls
- [ ] AI responses appear correctly
- [ ] Messages scroll automatically
- [ ] Dark/light theme compatibility
- [ ] Mobile responsive design
- [ ] Error messages display properly

### API Testing
```bash
# Test recommendation endpoint
curl -X GET http://127.0.0.1:8000/ai-assistant/recommendation \
  -H "Cookie: PHPSESSID=your_session_id"

# Test chat endpoint
curl -X POST http://127.0.0.1:8000/ai-assistant/chat \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d '{"question":"How am I doing?"}'
```

## Files Created/Modified

### New Files
- `src/Service/AI/AIRewardRecommendationService.php`
- `src/Controller/Front/AI/AIAssistantController.php`
- `templates/components/ai_chat_widget.html.twig`
- `docs/AI_REWARD_ASSISTANT.md` (this file)

### Modified Files
- `templates/front/users/student/dashboard.html.twig`
- `templates/front/game/index.html.twig`
- `templates/front/game/browse.html.twig`
- `templates/front/game/my_rewards.html.twig`
- `templates/front/game/favorites.html.twig`

## Dependencies

### PHP Packages
- `symfony/http-client` (for API calls)
- `psr/log` (for logging)

### JavaScript
- Vanilla JavaScript (no external libraries)
- Bootstrap Icons (for UI icons)

### CSS
- Custom CSS (embedded in component)
- Bootstrap 5 (for form controls)

## Security Considerations

### Authentication
- All endpoints require `ROLE_STUDENT`
- Session-based authentication
- No public access to AI features

### Input Validation
- Chat messages sanitized
- Maximum message length enforced
- XSS prevention in message display

### API Key Protection
- Stored in environment variables
- Never exposed to frontend
- Server-side API calls only

### Rate Limiting
- Consider implementing rate limits
- Prevent API abuse
- Monitor usage patterns

## Maintenance

### Regular Tasks
1. Monitor API usage and costs
2. Review error logs weekly
3. Update AI prompts based on feedback
4. Test with new reward types
5. Optimize response times

### Updates
- Keep Hugging Face API integration current
- Update model if better alternatives available
- Refine prompts based on user feedback
- Improve error handling as needed

## Support & Documentation

### For Developers
- Review code comments in service classes
- Check controller annotations for route details
- Examine Twig component for UI structure
- Test API endpoints with provided examples

### For Users
- Click the purple AI button to start chatting
- Use quick action buttons for common tasks
- Type questions naturally
- Wait for typing indicator to complete

## Conclusion

The AI Reward Assistant provides an intelligent, interactive way for students to engage with the gamification system. By offering personalized recommendations and answering questions in real-time, it enhances user experience and encourages continued participation in the platform.

The system is designed to be maintainable, extensible, and user-friendly, with clear separation of concerns and comprehensive error handling. Future enhancements can build upon this foundation to create an even more sophisticated AI-powered learning companion.
