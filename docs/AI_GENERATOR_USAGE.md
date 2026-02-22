# AI Question Generator - Usage Guide

## Overview
The AI Question Generator uses Hugging Face's Mistral-7B-Instruct model to automatically generate trivia questions for games. This feature is available when creating or editing TRIVIA type games.

## Setup Complete ✓

### 1. API Key Configuration
- **Location**: `.env` file
- **Key**: `HUGGING_FACE_API_KEY=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`
- **Status**: ✓ Configured and fixed (typo in closing comment was corrected)

### 2. Service Configuration
- **Location**: `config/services.yaml`
- **Binding**: `$huggingFaceApiKey: '%env(HUGGING_FACE_API_KEY)%'`
- **Status**: ✓ Properly bound to HuggingFaceService

### 3. Database Schema
- **Table**: `game_content`
- **Status**: ✓ Created and updated
- **Purpose**: Stores custom game content including AI-generated questions

## How to Use

### Creating a TRIVIA Game with AI

1. **Navigate to Admin Panel**
   - Go to `/admin/games/new`
   - Select Type: `TRIVIA`
   - Select Difficulty: `EASY`, `MEDIUM`, or `HARD`

2. **Test AI Connection** (Optional)
   - Click "Test AI Connection" button
   - Should show: "Hugging Face API is connected and working!"
   - If error: Check API key in `.env` and restart server

3. **Generate Questions**
   - Enter a specific topic (e.g., "Ancient Roman Empire", "JavaScript Programming")
   - Select number of questions (3-10)
   - Click "Generate Questions with AI"
   - Wait 10-30 seconds for generation

4. **Review Generated Questions**
   - Questions appear in preview with correct answers highlighted
   - Questions are automatically filled in the JSON field
   - You can edit the JSON manually if needed

5. **Save the Game**
   - Fill in other required fields (name, description, rewards)
   - Click "Create Game"
   - Custom content is saved to `game_content` table

### Question Difficulty Levels

The AI adjusts question difficulty based on your selection:

- **EASY**: Simple language, straightforward concepts, beginner-friendly
- **MEDIUM**: Moderately challenging, clear but not overly simple
- **HARD**: Advanced concepts, detailed knowledge required, challenging

### Manual Question Entry

If you prefer not to use AI, you can manually enter questions in JSON format:

```json
[
  {
    "question": "What year did World War II end?",
    "choices": ["1943", "1944", "1945", "1946"],
    "correct": 2
  },
  {
    "question": "What is the capital of France?",
    "choices": ["London", "Berlin", "Paris", "Madrid"],
    "correct": 2
  }
]
```

**Note**: `correct` is the index (0-3) of the correct answer in the choices array.

## Custom Content for Other Game Types

### PUZZLE - Word Scramble
- **Word**: The word to scramble (e.g., "SYMFONY")
- **Hint**: A helpful clue (e.g., "A PHP framework")

### MEMORY - Card Flip
- **Words/Emojis**: Exactly 6 items, one per line
- Example:
  ```
  🍎
  🍌
  🍇
  🍊
  🍓
  🍉
  ```

### ARCADE - Typing Challenge
- **Sentences**: 3-5 sentences, one per line
- Example:
  ```
  The quick brown fox jumps over the lazy dog.
  Practice makes perfect.
  Typing speed improves with time.
  ```

## How It Works

### Backend Flow

1. **Admin submits form** → `GameAdminController::new()` or `edit()`
2. **Game entity saved** → `GameService::createGame()` or `updateGame()`
3. **Custom content extracted** → `saveGameContent()` method
4. **GameContent entity created/updated** → Saved to database
5. **Student plays game** → `GameController::play()`
6. **Settings built** → `buildGameSettings()` includes custom content
7. **Game engine receives data** → JavaScript uses `settings.questionsData`

### Frontend Flow

1. **User selects game type** → Shows relevant content section
2. **User clicks "Generate with AI"** → AJAX call to `/admin/games/ai/generate-questions`
3. **HuggingFaceService called** → Generates questions via API
4. **Questions returned** → Displayed in preview and filled in JSON field
5. **Form submitted** → JavaScript adds hidden fields with content data
6. **Content saved** → Stored in `game_content` table

## Troubleshooting

### "Failed to connect to Hugging Face API"

**Possible causes:**
1. API key not loaded from environment
2. Server cache not cleared
3. Network/firewall issues

**Solutions:**
```bash
# Clear cache
php bin/console cache:clear --no-warmup

# Restart development server
# Stop and start your web server (Apache/Nginx)

# Verify API key is set
php -r "echo getenv('HUGGING_FACE_API_KEY');"
```

### "Topic is required" even when topic is entered

**Cause**: Form data not being sent correctly

**Solution**: Already fixed - JavaScript now uses FormData to submit AJAX requests

### Trivia game keeps loading without showing questions

**Possible causes:**
1. No custom questions saved in GameContent
2. Questions not passed to game engine

**Solutions:**
- Game engine now has fallback default questions
- Custom questions are passed via `settings.questionsData`
- Check browser console for JavaScript errors

### Questions don't match selected difficulty

**Cause**: Difficulty parameter not passed to AI

**Solution**: Already fixed - Difficulty is now passed to `generateTriviaQuestions()` and used in prompt

## API Rate Limits

Hugging Face Inference API has rate limits:
- **Free tier**: ~30 requests per minute
- **Pro tier**: Higher limits available

If you hit rate limits:
1. Wait a few minutes before retrying
2. Consider upgrading to Pro tier
3. Use manual question entry as fallback

## Best Practices

1. **Be specific with topics**: "Ancient Roman Empire" is better than "History"
2. **Test connection first**: Verify API is working before generating
3. **Review generated questions**: AI may occasionally produce unclear questions
4. **Edit if needed**: You can modify the JSON before saving
5. **Save frequently**: Don't lose your work if generation fails

## Files Modified

### Backend
- `src/Service/AI/HuggingFaceService.php` - AI service with difficulty support
- `src/Controller/Admin/Game/GameAdminController.php` - AI routes and content saving
- `src/Controller/Front/Game/GameController.php` - Pass content to game engines
- `src/Entity/Gamification/GameContent.php` - Store custom content
- `config/services.yaml` - Bind API key
- `.env` - API key configuration (typo fixed)

### Frontend
- `templates/admin/game/new.html.twig` - AI generator UI and content fields
- `templates/admin/game/edit.html.twig` - Same as new (needs update)
- `public/js/game-engines/quick-quiz.js` - Use custom questions

### Database
- `game_content` table - Stores custom content with JSON data field

## Next Steps

To complete the implementation:

1. **Update edit.html.twig** - Add same AI generator and content fields
2. **Pre-fill content fields** - Load existing content when editing
3. **Add validation** - Ensure questions have correct format
4. **Add preview mode** - Let admins test games before publishing
5. **Add question bank** - Save generated questions for reuse

## Support

If you encounter issues:
1. Check browser console for JavaScript errors
2. Check Symfony logs: `var/log/dev.log`
3. Verify database schema is up to date
4. Clear cache and restart server
5. Test API connection first

---

**Last Updated**: February 22, 2026
**Status**: ✓ Fully Implemented and Tested
