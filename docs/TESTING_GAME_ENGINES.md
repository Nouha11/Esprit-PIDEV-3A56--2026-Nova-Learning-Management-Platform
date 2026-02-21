# Testing Game Engines - Quick Guide

## How to Test the Game System

### Step 1: Create a Game from Template (Admin)

1. Login as admin
2. Navigate to: `http://127.0.0.1:8000/admin/games`
3. Click "Game Templates" button
4. Select "Word Scramble" (PUZZLE)
5. Choose "Easy" difficulty
6. Click "Create Game"
7. In the modal, keep the name or customize it
8. Click "Create Game"
9. You'll be redirected to the edit page
10. Make sure "Is Active?" is checked
11. Click "Update Game"

### Step 2: Play the Game (Student)

1. Logout from admin
2. Login as a student
3. Navigate to: `http://127.0.0.1:8000/games`
4. Find the game you just created
5. Click on it to view details
6. Click "Play Now"
7. The game engine should load automatically
8. Play the game!

### Step 3: Verify Game Completion

1. Complete the game (score at least 60%)
2. You should see a completion screen
3. Click "Back to Games"
4. Verify you received the rewards (tokens + XP)

## Available Game Engines

### Currently Working:
1. **Word Scramble** (PUZZLE) - ✅ Fully functional
2. **Memory Match** (MEMORY) - ✅ Fully functional
3. **Breathing Exercise** (Mini Game) - ✅ Fully functional

### To Be Implemented:
4. Quick Quiz (TRIVIA)
5. Reaction Clicker (ARCADE)
6. Quick Stretch (Mini Game)
7. Eye Rest (Mini Game)
8. Hydration Break (Mini Game)

## Troubleshooting

### Game doesn't load:
- Check browser console for JavaScript errors
- Verify the game engine file exists in `public/js/game-engines/`
- Check that the CSS file is loaded: `public/assets/css/game-engines.css`

### Game engine not found:
- The game description should contain `[Engine: engine_name]`
- Check the game in admin panel and verify the description

### Rewards not awarded:
- Check that the game completion reaches 60% or higher
- Verify the `completeGame()` function is called
- Check browser network tab for the completion POST request

## Creating More Games

You can create multiple games from the same template with different difficulties:

1. Word Scramble - Easy (5 words, 60s)
2. Word Scramble - Medium (8 words, 45s)
3. Word Scramble - Hard (12 words, 30s)

Each will have different rewards based on difficulty!

## Next Steps

To add more game engines:
1. Create JavaScript file in `public/js/game-engines/`
2. Follow the pattern from existing engines
3. Add template definition in `GameTemplateService.php`
4. Add loading logic in `play.html.twig`

## Demo Data

For quick testing, you can create these games:
- Easy Word Scramble (10T / 20XP)
- Medium Memory Match (20T / 40XP)
- Hard Word Scramble (30T / 60XP)
- Breathing Exercise (5 Energy)
