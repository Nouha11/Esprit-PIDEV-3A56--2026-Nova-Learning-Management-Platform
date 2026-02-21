# Game Generator System

## Overview
A simple system that allows admins to generate playable games from templates without coding.

## Game Templates Available

### Full Games (with rewards)
1. **PUZZLE - Word Scramble**: Unscramble words within time limit
2. **MEMORY - Card Match**: Classic memory card matching game
3. **TRIVIA - Quick Quiz**: Multiple choice questions
4. **ARCADE - Reaction Clicker**: Click targets before they disappear

### Mini Games (energy regeneration)
1. **Breathing Exercise**: Calm breathing animation
2. **Quick Stretch**: Simple stretch timer
3. **Eye Rest**: 20-20-20 rule reminder
4. **Hydration Reminder**: Water break timer

## How It Works

1. Admin selects game template from dropdown
2. System auto-fills game configuration
3. Admin customizes:
   - Name
   - Difficulty (affects time limits, complexity)
   - Rewards (tokens/XP)
   - Theme colors
4. Game is instantly playable

## Technical Implementation

- Game templates stored as JSON configurations
- JavaScript-based game engines (no backend complexity)
- Dark/Light theme support built-in
- Mobile responsive
- No external dependencies needed

## File Structure
```
src/Service/game/GameTemplateService.php - Template definitions
templates/front/game/engines/ - Game engine templates
public/js/game-engines/ - JavaScript game logic
```
