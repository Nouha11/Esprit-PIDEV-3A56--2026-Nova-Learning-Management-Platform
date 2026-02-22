# Testing Trivia Game with AI Questions

## Issue Summary
Questions are being generated and saved to database correctly, but not loading when student plays the game.

## Root Cause
**Data format mismatch** between database and JavaScript:
- Database stores: `{"question": "...", "choices": [...], "correct": 0}`
- JavaScript expected: `{"question": "...", "options": [...], "correctAnswer": 0}`

## Fix Applied
Updated `public/js/game-engines/quick-quiz.js` to normalize both formats:
```javascript
questions = settings.questionsData.map(q => ({
    question: q.question,
    options: q.options || q.choices || [],  // Handle both formats
    correctAnswer: q.correctAnswer !== undefined ? q.correctAnswer : q.correct
}));
```

## Testing Steps

### 1. Verify Questions in Database
```bash
php bin/console doctrine:query:sql "SELECT g.id, g.name, gc.data FROM game g LEFT JOIN game_content gc ON g.id = gc.game_id WHERE g.type = 'TRIVIA' ORDER BY g.id DESC LIMIT 1"
```

Expected: Should show JSON with questions array

### 2. Test Game Play
1. Navigate to: `http://127.0.0.1:8000/games`
2. Find a TRIVIA game (e.g., "GAMES QUIZ" - ID 25)
3. Click "Play Now"
4. **Open Browser Console** (F12 → Console tab)
5. Look for debug logs:
   ```
   Quick Quiz Engine Started
   Game ID: 25
   Settings: {questions: 5, timeLimit: 60, difficulty: "MEDIUM", questionsData: Array(5)}
   Has questionsData: true
   Number of questions: 5
   Loaded custom questions: Array(5)
   ```

### 3. Expected Behavior
- ✓ Game loads immediately (no infinite spinner)
- ✓ First question appears with 4 answer choices
- ✓ Timer starts counting down
- ✓ Can click answers
- ✓ Shows correct/incorrect feedback
- ✓ Moves to next question
- ✓ Shows final score after all questions

### 4. If Still Not Working

#### Check Browser Console
Look for errors like:
- `Cannot read property 'length' of undefined` → questionsData not passed
- `JSON.parse error` → Settings not properly encoded
- `questions is not iterable` → Wrong data format

#### Check PHP Error Log
Location: `var/log/dev.log`

Look for the debug output we added:
```
Game ID: 25
Game Type: TRIVIA
Has Content: YES
Content Data: {"topic":"games","questions":[...]}
Game Settings: {"questions":5,"timeLimit":60,"difficulty":"MEDIUM","questionsData":[...]}
```

If "Has Content: NO" → Content not being loaded from database

#### Check Network Tab
1. Open DevTools → Network tab
2. Reload game page
3. Find the HTML response
4. Search for `data-settings=`
5. Verify it contains `questionsData`

### 5. Create New Test Game

To verify the full flow:

1. Go to `/admin/games/new`
2. Fill in:
   - Name: "Test Quiz"
   - Type: TRIVIA
   - Difficulty: EASY
   - Category: FULL_GAME
   - Rewards: 10 tokens, 20 XP
3. In AI Generator section:
   - Topic: "Simple Math"
   - Count: 3
   - Click "Generate Questions with AI"
4. Wait for questions to appear
5. Click "Create Game"
6. As student, play the game
7. Verify questions appear

## Debug Checklist

- [ ] Cache cleared: `php bin/console cache:clear --no-warmup`
- [ ] Questions in database (check SQL query above)
- [ ] Browser console shows debug logs
- [ ] No JavaScript errors in console
- [ ] `data-settings` attribute contains `questionsData`
- [ ] PHP error log shows "Has Content: YES"
- [ ] Game loads without infinite spinner
- [ ] Questions appear and are playable

## Common Issues

### Issue: "Has Content: NO" in logs
**Cause**: GameContent entity not being loaded with Game entity

**Fix**: Check if relationship is properly configured in Game entity:
```php
#[ORM\OneToOne(mappedBy: 'game', targetEntity: GameContent::class, cascade: ['persist', 'remove'])]
private ?GameContent $content = null;
```

### Issue: Questions appear but are all the same
**Cause**: Array not being properly iterated

**Fix**: Already handled by normalizing format in quick-quiz.js

### Issue: "correctAnswer is undefined"
**Cause**: Database uses `correct` but JS expects `correctAnswer`

**Fix**: Already handled by the normalization:
```javascript
correctAnswer: q.correctAnswer !== undefined ? q.correctAnswer : q.correct
```

### Issue: Infinite loading spinner
**Cause**: JavaScript file not loading or crashing before init()

**Fix**: Check browser console for errors, verify file path in play.html.twig

## API Key Question

**Q: Do I still need the Hugging Face API key?**

**A: YES!** The API key is still required and being used. Here's why:

1. **You're using Novita's router** which proxies to Hugging Face models
2. **The router requires authentication** via your HF API key
3. **The key is in your .env**: `HUGGING_FACE_API_KEY=hf_your_token_here`
4. **It's properly injected** via `config/services.yaml`

The Novita router is just a different endpoint that provides better reliability than the old HF Inference API, but it still uses your HF credentials.

## What Changed in Your Setup

### Old (Broken):
- API URL: `https://api-inference.huggingface.co/models/mistralai/Mistral-7B-Instruct-v0.1`
- Status: 410 Gone (deprecated)
- Request format: `{'inputs': prompt}`
- Response format: `[{'generated_text': '...'}]`

### New (Working):
- API URL: `https://router.huggingface.co/novita/v3/openai/chat/completions`
- Status: Active
- Request format: OpenAI-compatible chat completions
- Response format: `{'choices': [{'message': {'content': '...'}}]}`
- Model: `qwen/qwen2.5-7b-instruct` (case-sensitive!)

## Next Steps

1. Test the game with browser console open
2. Verify debug logs appear
3. If questions still don't load, share:
   - Browser console output
   - PHP error log output
   - Network tab showing `data-settings` value

## Files Modified in This Fix

1. `public/js/game-engines/quick-quiz.js`
   - Added format normalization
   - Added debug logging
   - Handles both `choices`/`correct` and `options`/`correctAnswer`

2. `src/Controller/Front/Game/GameController.php`
   - Added debug logging to see what's being loaded

3. Cache cleared

---

**Status**: Fix applied, ready for testing
**Expected Result**: Questions should now load and display correctly
