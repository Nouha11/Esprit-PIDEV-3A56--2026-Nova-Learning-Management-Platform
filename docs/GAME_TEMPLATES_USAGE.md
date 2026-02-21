# Game Templates - Usage Guide

## Overview
The Game Template system allows admins to create playable games instantly without coding. Each template is a pre-built, fully functional game that can be customized.

## Accessing Game Templates

1. Navigate to: `/admin/games`
2. Click the "Game Templates" button
3. Browse available templates

## Available Templates

### Full Games (Reward-Based)

#### 1. Word Scramble (PUZZLE)
- **Description**: Unscramble words within time limit
- **Difficulty Levels**:
  - Easy: 5 words, 60 seconds (10T / 20XP)
  - Medium: 8 words, 45 seconds (20T / 40XP)
  - Hard: 12 words, 30 seconds (30T / 60XP)
- **Engine**: `word_scramble`

#### 2. Memory Match (MEMORY)
- **Description**: Match pairs of cards
- **Difficulty Levels**:
  - Easy: 6 pairs, 90 seconds (10T / 20XP)
  - Medium: 10 pairs, 120 seconds (20T / 40XP)
  - Hard: 15 pairs, 150 seconds (30T / 60XP)
- **Engine**: `memory_match`

#### 3. Quick Quiz (TRIVIA)
- **Description**: Answer multiple choice questions
- **Difficulty Levels**:
  - Easy: 5 questions, 15s each (10T / 20XP)
  - Medium: 8 questions, 12s each (20T / 40XP)
  - Hard: 10 questions, 10s each (30T / 60XP)
- **Engine**: `quick_quiz`

#### 4. Reaction Clicker (ARCADE)
- **Description**: Click targets before they disappear
- **Difficulty Levels**:
  - Easy: 10 targets, 2s each (10T / 20XP)
  - Medium: 15 targets, 1.5s each (20T / 40XP)
  - Hard: 20 targets, 1s each (30T / 60XP)
- **Engine**: `reaction_clicker`

### Mini Games (Energy Regeneration)

#### 1. Breathing Exercise
- **Energy**: +5 points
- **Duration**: ~40 seconds (3 cycles)
- **Engine**: `breathing`

#### 2. Quick Stretch
- **Energy**: +5 points
- **Duration**: ~60 seconds
- **Engine**: `stretch`

#### 3. Eye Rest (20-20-20 Rule)
- **Energy**: +3 points
- **Duration**: 20 seconds
- **Engine**: `eye_rest`

#### 4. Hydration Break
- **Energy**: +3 points
- **Duration**: 30 seconds
- **Engine**: `hydration`

## Creating a Game from Template

### Step 1: Select Template
1. Go to Game Templates page
2. Choose a template (Full Game or Mini Game)
3. For Full Games: Select difficulty level

### Step 2: Customize Name
1. Click "Create Game" button
2. Modal appears with default name
3. Customize the name or keep default
4. Click "Create Game"

### Step 3: Further Customization
1. System redirects to game edit page
2. Customize additional settings:
   - Description
   - Token cost to play
   - Rewards (for full games)
   - Active status

### Step 4: Activate
1. Set game to "Active"
2. Save changes
3. Game is now playable by students!

## Technical Details

### File Structure
```
src/Service/game/GameTemplateService.php - Template definitions
templates/admin/game/templates.html.twig - Template selection UI
public/js/game-engines/ - JavaScript game engines
  - word-scramble.js
  - memory-match.js
  - breathing.js
  - (more to come)
public/assets/css/game-engines.css - Game styling
```

### Game Engines
Each game engine is a standalone JavaScript class that:
- Initializes with settings
- Renders the game UI
- Handles game logic
- Supports dark/light themes
- Reports completion status

### Adding New Templates

To add a new game template:

1. **Define Template** in `GameTemplateService.php`:
```php
'my_game' => [
    'name' => 'My Game',
    'type' => 'PUZZLE',
    'category' => 'FULL_GAME',
    'description' => 'Game description',
    'engine' => 'my_game_engine',
    'difficulty_settings' => [
        'EASY' => ['time' => 60, 'tokens' => 10, 'xp' => 20],
        // ...
    ],
],
```

2. **Create Engine** in `public/js/game-engines/my-game-engine.js`:
```javascript
class MyGameEngine {
    constructor(containerId, settings) {
        // Initialize
    }
    
    init() {
        // Start game
    }
    
    endGame(passed) {
        // Complete game
        if (typeof window.completeGame === 'function') {
            window.completeGame(passed, score, maxScore);
        }
    }
}
```

3. **Add Styles** in `public/assets/css/game-engines.css`

## Theme Support

All game engines automatically support dark/light themes:
- Check theme: `document.documentElement.getAttribute('data-bs-theme')`
- Apply conditional styling based on theme
- Use CSS variables for colors

## Best Practices

1. **Keep Games Simple**: Focus on core mechanics
2. **Clear Instructions**: Tell players what to do
3. **Visual Feedback**: Show progress and results
4. **Mobile Responsive**: Test on different screen sizes
5. **Accessibility**: Use proper contrast and font sizes

## Future Enhancements

- AI-generated quiz questions
- Multiplayer support
- Leaderboards per game
- Custom game themes
- More game templates
- Game analytics dashboard
