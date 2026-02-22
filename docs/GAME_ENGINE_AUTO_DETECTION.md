# Game Engine Auto-Detection Fix

## Issue
Games created manually (not from templates) don't have the `[Engine: xxx]` tag in their description, causing the game to show an infinite loading spinner because no JavaScript engine file is loaded.

## Root Cause
The `GameController::play()` method was only checking the description field for the engine tag:
```php
if (preg_match('/\[Engine: ([^\]]+)\]/', $game->getDescription(), $matches)) {
    $gameEngine = $matches[1];
}
```

If the description didn't contain the tag, `$gameEngine` remained as `'default'`, and no game engine JavaScript file was loaded.

## Solution
Added a fallback that automatically determines the engine based on the game type:

```php
// Extract game engine from description or determine from game type
$gameEngine = 'default';

if (preg_match('/\[Engine: ([^\]]+)\]/', $game->getDescription(), $matches)) {
    $gameEngine = $matches[1];
} else {
    // Fallback: Determine engine from game type
    $gameEngine = match($game->getType()) {
        'PUZZLE' => 'word_scramble',
        'MEMORY' => 'memory_match',
        'TRIVIA' => 'quick_quiz',
        'ARCADE' => 'reaction_clicker',
        default => 'default'
    };
}
```

## Game Type to Engine Mapping

| Game Type | Engine File | JavaScript Path |
|-----------|-------------|-----------------|
| PUZZLE | word_scramble | `/js/game-engines/word-scramble.js` |
| MEMORY | memory_match | `/js/game-engines/memory-match.js` |
| TRIVIA | quick_quiz | `/js/game-engines/quick-quiz.js` |
| ARCADE | reaction_clicker | `/js/game-engines/reaction-clicker.js` |

## How It Works Now

### Scenario 1: Game from Template
- Description: "Answer questions correctly [Engine: quick_quiz]"
- Engine extracted from description: `quick_quiz`
- JavaScript loaded: `quick-quiz.js` ✓

### Scenario 2: Manually Created Game
- Description: "Test your knowledge" (no engine tag)
- Game Type: TRIVIA
- Engine determined from type: `quick_quiz`
- JavaScript loaded: `quick-quiz.js` ✓

### Scenario 3: AI-Generated Game
- Description: "" (empty)
- Game Type: TRIVIA
- Engine determined from type: `quick_quiz`
- JavaScript loaded: `quick-quiz.js` ✓

## Files Modified

### `src/Controller/Front/Game/GameController.php`
- Added fallback logic using `match()` expression
- Maps game type to appropriate engine
- Added engine to debug logs

## Testing

### Before Fix
1. Create TRIVIA game without engine tag in description
2. Try to play game
3. Result: Infinite loading spinner ❌

### After Fix
1. Create TRIVIA game without engine tag in description
2. Try to play game
3. Result: Game loads and questions appear ✓

## Verification

Check the PHP error log for debug output:
```
Game ID: 25
Game Type: TRIVIA
Game Engine: quick_quiz
Has Content: YES
Content Data: {"topic":"games","questions":[...]}
Game Settings: {"questions":5,"timeLimit":60,"difficulty":"MEDIUM","questionsData":[...]}
```

The `Game Engine: quick_quiz` line confirms the engine was correctly determined.

## Benefits

✓ **Backward Compatible**: Games with engine tags still work  
✓ **Auto-Detection**: New games work without manual tagging  
✓ **Consistent**: All games of same type use same engine  
✓ **Maintainable**: Single source of truth for type→engine mapping  

## Impact

This fix affects:
- ✓ All manually created games
- ✓ All AI-generated games
- ✓ Games created via admin form
- ✓ Games without description

Does NOT affect:
- ✓ Games created from templates (still use description tag)
- ✓ Existing working games

## Future Improvements

Consider:
1. Remove engine tag from description entirely
2. Always use game type for engine determination
3. Add `engine` field to Game entity for explicit control
4. Validate engine exists before loading

## Related Files

- `src/Controller/Front/Game/GameController.php` - Engine detection logic
- `templates/front/game/play.html.twig` - Engine loading template
- `public/js/game-engines/*.js` - Game engine implementations

---

**Status**: ✓ Fixed  
**Date**: February 22, 2026  
**Impact**: All game types now work regardless of description content
