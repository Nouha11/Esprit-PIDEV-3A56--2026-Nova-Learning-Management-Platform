# AI API Configuration Guide

## Overview

The AIRecommendationService now supports both **OpenAI** and **Google Gemini** APIs with automatic fallback. The service will try OpenAI first, and if it fails or is unavailable, it will automatically fall back to Gemini.

## Configuration

### Environment Variables

Add both API keys to your `.env` file:

```env
# OpenAI API (Primary)
OPENAI_API_KEY=your_openai_api_key_here

# Google Gemini API (Fallback)
GEMINI_API_KEY=your_gemini_api_key_here
```

### API Key Priority

1. **OpenAI** is tried first (if configured)
2. **Gemini** is used as fallback (if OpenAI fails or is not configured)
3. **Generic recommendations** are returned if both APIs fail

## Getting API Keys

### OpenAI API Key

1. Go to [OpenAI Platform](https://platform.openai.com/)
2. Sign up or log in
3. Navigate to **API Keys** section
4. Click **Create new secret key**
5. Copy the key and add it to your `.env` file

**Note**: OpenAI API is pay-as-you-go. You'll need to add billing information.

### Google Gemini API Key

1. Go to [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Sign in with your Google account
3. Click **Get API Key** or **Create API Key**
4. Copy the key and add it to your `.env` file

**Note**: Gemini API has a generous free tier (60 requests per minute).

## Features Supported

Both APIs support all AI features:

- ✅ Study recommendations based on session data
- ✅ Note summarization
- ✅ Quiz generation from content

## API Comparison

| Feature | OpenAI (GPT-3.5) | Google Gemini Pro |
|---------|------------------|-------------------|
| **Cost** | Pay-as-you-go (~$0.002/1K tokens) | Free tier available |
| **Rate Limit** | Varies by plan | 60 requests/minute (free) |
| **Quality** | Excellent | Excellent |
| **Response Time** | Fast | Fast |
| **Availability** | High | High |

## How It Works

### Automatic Fallback

```
User Request
    ↓
Check if OpenAI key is configured
    ↓ Yes
Try OpenAI API
    ↓ Success? → Return response
    ↓ Failure
Check if Gemini key is configured
    ↓ Yes
Try Gemini API
    ↓ Success? → Return response
    ↓ Failure
Return generic recommendations
```

### Error Handling

The service includes comprehensive error handling:

- **Circuit Breaker**: After 3 consecutive failures, the API is temporarily disabled
- **Caching**: Successful responses are cached for 1 hour
- **Graceful Degradation**: Generic recommendations are provided if all APIs fail
- **Detailed Logging**: All errors are logged for debugging

## Testing Your Configuration

### Test Note Summarization

1. Navigate to: `http://127.0.0.1:8000/study-session/integration/ai/recommendations`
2. Enter some note content (at least 50 characters)
3. Click "Summarize Notes"
4. You should see a summary generated

### Test Study Recommendations

1. Complete a few study sessions
2. Navigate to: `http://127.0.0.1:8000/study-session/integration/ai/recommendations`
3. You should see personalized recommendations

### Test Quiz Generation

1. Navigate to the AI recommendations page
2. Enter content for quiz generation (at least 100 characters)
3. Click "Generate Quiz"
4. You should see 5-10 quiz questions

## Troubleshooting

### Error: "AI service is not configured"

**Cause**: Neither OpenAI nor Gemini API keys are configured.

**Solution**: Add at least one API key to your `.env` file.

### Error: "Failed to generate summary. The API may be unavailable"

**Cause**: Both APIs failed or returned errors.

**Possible reasons**:
- Invalid API keys
- API quota exceeded
- Network connectivity issues
- API service temporarily down

**Solution**:
1. Check your API keys are valid
2. Check your API usage/quota
3. Check the logs: `var/log/dev.log`
4. Try again in a few minutes

### Error: "AI service is temporarily unavailable due to repeated failures"

**Cause**: Circuit breaker is open after 3 consecutive failures.

**Solution**: Wait a few minutes for the circuit breaker to reset, then try again.

### Checking Logs

View detailed error logs:

```bash
# Windows PowerShell
Get-Content var/log/dev.log -Tail 50 | Select-String "AIRecommendation"

# Linux/Mac
tail -f var/log/dev.log | grep AIRecommendation
```

## Recommendations

### For Development

Use **Gemini API** (free tier):
- No billing required
- Generous free quota
- Good for testing and development

### For Production

Consider **OpenAI API**:
- More predictable pricing
- Better rate limits for high-volume usage
- Established reliability

### Best Practice

Configure **both APIs**:
- Use OpenAI as primary
- Use Gemini as fallback
- Ensures high availability

## Cost Optimization

### Caching

All AI responses are cached for 1 hour, reducing API calls:
- Same note summarization request within 1 hour = cached response
- Same study recommendations within 1 hour = cached response
- Same quiz generation within 1 hour = cached response

### Rate Limiting

The service includes built-in rate limiting:
- 10-second timeout per request
- Circuit breaker after 3 failures
- Automatic fallback to alternative API

## Security

### API Key Protection

- Never commit API keys to version control
- Use `.env` file (already in `.gitignore`)
- Rotate keys periodically
- Monitor API usage for anomalies

### API Key Restrictions

**OpenAI**:
- Set up IP restrictions in OpenAI dashboard
- Monitor usage in OpenAI dashboard

**Gemini**:
- Set up application restrictions in Google Cloud Console
- Monitor usage in Google AI Studio

## Support

For issues or questions:
- Check the logs first
- Verify API keys are valid
- Test API connectivity manually
- Contact support if issues persist

## Updates

Last updated: 2024
Service version: 1.0
Supported APIs: OpenAI GPT-3.5, Google Gemini Pro
