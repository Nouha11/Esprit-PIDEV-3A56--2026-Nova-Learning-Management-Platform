# NOVA Translation System Setup

This document explains how to set up and use the OpenAI-powered translation system for the NOVA platform.

## Setup Instructions

### 1. Get OpenAI API Key
1. Go to [OpenAI Platform](https://platform.openai.com/)
2. Create an account or log in
3. Navigate to API Keys section
4. Create a new API key
5. Copy the API key

### 2. Configure Environment
1. Open your `.env` file
2. Replace `your_openai_api_key_here` with your actual OpenAI API key:
   ```
   OPENAI_API_KEY=sk-your-actual-api-key-here
   ```

### 3. Test the Translation
1. Visit: `http://your-domain/test-translation?text=Hello World&lang=fr`
2. You should see a JSON response with the translated text

## How to Use Translation in Templates

### Method 1: Using the Translation Macro
```twig
{% import 'macros/translation.html.twig' as t %}

<h1>{{ t.t('Welcome to NOVA') }}</h1>
<p>{{ t.t('This text will be translated to French when French is selected') }}</p>
```

### Method 2: Using Twig Functions Directly
```twig
<h1>{{ translate_text('Welcome to NOVA') }}</h1>

{% if is_french() %}
    <p>{{ translate_text('This is in French') }}</p>
{% else %}
    <p>This is in English</p>
{% endif %}
```

### Method 3: Conditional Translation
```twig
<button class="btn btn-primary">
    {% if is_french() %}
        {{ translate_text('Save Changes') }}
    {% else %}
        Save Changes
    {% endif %}
</button>
```

## Language Switcher

The language switcher is automatically included in the main navigation. Users can switch between:
- 🇺🇸 English (default)
- 🇫🇷 Français

## Available Twig Functions

- `translate_text(text)` - Translates text to the current locale
- `current_locale()` - Returns current language ('en' or 'fr')
- `is_french()` - Returns true if current language is French

## Translation Service Methods

### PHP Service Usage
```php
use App\Service\TranslationService;

public function someAction(TranslationService $translationService)
{
    // Translate single text
    $translated = $translationService->translateText('Hello World', 'fr');
    
    // Translate array of texts
    $texts = ['Hello', 'World', 'Welcome'];
    $translated = $translationService->translateArray($texts, 'fr');
}
```

## Performance Notes

- Translations are cached in the session to avoid repeated API calls
- The system falls back to original text if translation fails
- API calls are made only when French is selected
- Consider implementing a local cache for frequently used translations

## Cost Optimization

- OpenAI API charges per token
- Short texts are more cost-effective
- Consider pre-translating common UI elements
- Monitor API usage in OpenAI dashboard

## Supported Languages

Currently configured for:
- English (en) - Default
- French (fr)

To add more languages, update:
1. `LanguageController` route requirements
2. `TranslationService` language mapping
3. Language switcher component
4. Twig extension logic

## Troubleshooting

### Translation Not Working
1. Check if OpenAI API key is set correctly
2. Verify internet connection
3. Check OpenAI API quota/billing
4. Look at browser console for errors

### Language Not Switching
1. Clear browser cache
2. Check session storage
3. Verify route is accessible

### Performance Issues
1. Check API response times
2. Consider implementing caching
3. Monitor OpenAI API usage