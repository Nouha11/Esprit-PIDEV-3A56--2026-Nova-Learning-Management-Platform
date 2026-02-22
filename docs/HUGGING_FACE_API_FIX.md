# Hugging Face API Key Expired - Fix Guide

## Problem
All Hugging Face AI services stopped working with error:
```
User Access Token "NOVA Quiz Generator" is expired
```

## Affected Features
- Game AI Assistant (purple widget)
- Study Buddy AI (green widget)
- AI Question Generator for games
- AI Reward Recommendations

## Solution

### Step 1: Generate New API Key

1. Go to [Hugging Face Settings - Access Tokens](https://huggingface.co/settings/tokens)
2. Log in with your Hugging Face account
3. Click "New token" or "Create new token"
4. Give it a name (e.g., "NOVA Quiz Generator v2")
5. Select permissions: **Read** access is sufficient
6. Click "Generate token"
7. **Copy the token immediately** (you won't be able to see it again)

### Step 2: Update .env File

1. Open `.env` file in the project root
2. Find the line:
   ```
   HUGGING_FACE_API_KEY=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
   ```
3. Replace with your new token:
   ```
   HUGGING_FACE_API_KEY=hf_YOUR_NEW_TOKEN_HERE
   ```
4. Save the file

### Step 3: Clear Cache

Run this command in your terminal:
```bash
php bin/console cache:clear --no-warmup
```

### Step 4: Test the AI Services

1. Go to any game page and click the purple AI Assistant widget
2. Ask a question like "Tell me about my progress"
3. Go to any study session page and click the green Study Buddy AI widget
4. Ask a question like "Give me study tips"

Both should now work correctly.

## API Configuration Details

The application uses:
- **Endpoint**: `https://router.huggingface.co/novita/v3/openai/chat/completions`
- **Model**: `qwen/qwen2.5-7b-instruct` (case-sensitive)
- **Temperature**: 0.3
- **Max Tokens**: 300 (chat), 2000 (question generation)
- **Timeout**: 20 seconds (chat), 30 seconds (questions)

## Error Messages

After the fix, users will see clearer error messages:
- **401 Error**: "Hugging Face API key is invalid or expired. Please update your API key."
- **Other Errors**: Specific error message from the API

## Files Modified

1. `src/Service/game/HuggingFaceService.php` - Improved error handling
2. `src/Service/game/AIRewardRecommendationService.php` - Improved error handling
3. `src/Controller/Front/StudySession/StudyBuddyController.php` - Fixed service injection

## Prevention

- API tokens can expire based on Hugging Face's policies
- Consider setting a reminder to refresh the token periodically
- Monitor the application logs for 401 errors
- Keep a backup token ready for quick replacement

## Support

If issues persist after updating the API key:
1. Check that the new token has Read permissions
2. Verify the token is correctly copied (no extra spaces)
3. Ensure cache was cleared
4. Check `var/log/dev.log` for detailed error messages
5. Try the test connection feature in admin panel (if available)
