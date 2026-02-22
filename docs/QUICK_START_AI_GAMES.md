# Quick Start: AI-Powered Game Creation

## 🎮 Create a Trivia Game with AI in 3 Minutes

### Step 1: Navigate to Game Creation
```
http://127.0.0.1:8000/admin/games/new
```

### Step 2: Fill Basic Info
- **Name**: "JavaScript Quiz"
- **Type**: TRIVIA
- **Description**: "Test your JavaScript knowledge"
- **Difficulty**: MEDIUM
- **Category**: FULL_GAME
- **Token Cost**: 0 (free)
- **Reward Tokens**: 20
- **Reward XP**: 40

### Step 3: Generate Questions with AI
1. Scroll to "AI Question Generator" section
2. Enter topic: "JavaScript Programming"
3. Select: 5 questions
4. Click "Test AI Connection" (optional, first time only)
5. Click "Generate Questions with AI"
6. Wait 10-30 seconds
7. Review generated questions
8. Edit if needed (questions appear in JSON field)

### Step 4: Save and Test
1. Click "Create Game"
2. Navigate to `/games` as student
3. Find your game
4. Click "Play Now"
5. Answer questions
6. Earn rewards!

---

## 🔧 Troubleshooting

### API Connection Failed
```bash
# Check .env file has correct API key
# Clear cache
php bin/console cache:clear --no-warmup

# Restart web server
```

### Game Keeps Loading
- Ensure questions were saved (check JSON field before submitting)
- Clear browser cache
- Check browser console for errors

### Questions Too Easy/Hard
- Change difficulty level before generating
- Regenerate questions
- Or manually edit the JSON

---

## 📝 Manual Question Format

If AI is unavailable, use this JSON format:

```json
[
  {
    "question": "What is a closure in JavaScript?",
    "choices": [
      "A function inside another function",
      "A loop structure",
      "A variable type",
      "An error handler"
    ],
    "correct": 0
  },
  {
    "question": "What does 'use strict' do?",
    "choices": [
      "Makes code faster",
      "Enables strict mode",
      "Disables errors",
      "Adds comments"
    ],
    "correct": 1
  }
]
```

**Note**: `correct` is the index (0-3) of the right answer.

---

## 🎯 Other Game Types

### PUZZLE - Word Scramble
- **Word**: JAVASCRIPT
- **Hint**: A programming language

### MEMORY - Card Match
- **Words** (6 items, one per line):
```
🎮
🎯
🎲
🎪
🎨
🎭
```

### ARCADE - Typing Speed
- **Sentences** (3-5, one per line):
```
The quick brown fox jumps over the lazy dog.
JavaScript is a versatile programming language.
Practice makes perfect in coding.
```

---

## ✅ What's Fixed

1. ✓ API connection working
2. ✓ Questions save to database
3. ✓ Questions load in game
4. ✓ Difficulty levels work
5. ✓ Fallback questions if no custom content

---

## 📚 Full Documentation

- `docs/AI_GENERATOR_USAGE.md` - Complete guide
- `FIXES_SUMMARY.md` - Technical details
- `docs/GAME_GENERATOR_SYSTEM.md` - System overview

---

**Ready to create games!** 🚀
