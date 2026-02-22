# Game Edit Page Enhanced

## What Was Added

The game edit page now has the same AI generator and custom content features as the create page, plus it pre-fills existing content for editing.

## New Features in Edit Page

### 1. AI Question Generator (TRIVIA games)
- ✅ Generate new questions with AI
- ✅ Test AI connection
- ✅ Preview generated questions
- ✅ Same functionality as create page

### 2. Custom Content Fields (All game types)
- ✅ PUZZLE: Word and hint fields
- ✅ MEMORY: 6 words/emojis
- ✅ TRIVIA: Topic and questions JSON
- ✅ ARCADE: 3-5 sentences

### 3. Pre-filled Existing Content
- ✅ Loads existing content from database
- ✅ Displays in appropriate fields
- ✅ Can be edited and updated
- ✅ Preserves content if not changed

## How It Works

### Editing Existing Game

1. **Navigate to edit page**: `/admin/games/{id}/edit`
2. **Existing content loads automatically**:
   - PUZZLE: Word and hint appear in fields
   - MEMORY: Words appear in textarea (one per line)
   - TRIVIA: Topic and questions JSON appear
   - ARCADE: Sentences appear in textarea

3. **Make changes**:
   - Edit existing content
   - Generate new AI questions (TRIVIA only)
   - Add content if none exists
   - Change game type (content fields update)

4. **Save**: Click "Update Game"
   - Content saved to `game_content` table
   - Existing content updated or created

### Adding Content to Empty Game

If a game was created without content:

1. Go to edit page
2. Select game type (if not set)
3. Fill in custom content fields
4. Save

The game will now have content and work properly!

## Pre-fill Logic

### PUZZLE
```twig
<input value="{{ existingContent.word ?? '' }}">
<input value="{{ existingContent.hint ?? '' }}">
```

### MEMORY
```twig
<textarea>{{ existingContent.words|default([])|join('\n') }}</textarea>
```

### TRIVIA
```twig
<input value="{{ existingContent.topic ?? '' }}">
<textarea>{{ existingContent.questions|default([])|json_encode(constant('JSON_PRETTY_PRINT')) }}</textarea>
```

### ARCADE
```twig
<textarea>{{ existingContent.sentences|default([])|join('\n') }}</textarea>
```

## Example: Editing Game ID 25

### Before
- Game ID: 25
- Name: GAMES QUIZ
- Type: TRIVIA
- Content: 5 AI-generated questions about games

### Edit Process
1. Go to `/admin/games/25/edit`
2. See existing questions in JSON field
3. See topic "games" in topic field
4. Can:
   - Edit existing questions
   - Generate new questions
   - Change topic
   - Add more questions

### After Save
- Content updated in database
- Students see updated questions when playing

## JavaScript Features

### Dynamic Sections
```javascript
// Show/hide based on game type
function updateSections() {
    const gameType = typeField.value;
    
    // Show AI generator only for TRIVIA
    aiSection.style.display = gameType === 'TRIVIA' ? 'block' : 'none';
    
    // Show custom content for all types
    contentSection.style.display = gameType ? 'block' : 'none';
    
    // Show relevant content fields
    document.getElementById('content-' + gameType.toLowerCase()).style.display = 'block';
}
```

### Form Submission
```javascript
// Add hidden fields on submit
gameForm.addEventListener('submit', function(e) {
    const gameType = typeField.value;
    
    if (gameType === 'TRIVIA') {
        addHidden('content_topic', document.getElementById('content-topic').value);
        addHidden('content_questions', document.getElementById('content-questions').value);
    }
    // ... other types
});
```

## Backend Processing

### GameAdminController::edit()
```php
public function edit(Request $request, Game $game, EntityManagerInterface $em): Response
{
    $form = $this->createForm(GameFormType::class, $game);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $this->gameService->updateGame($game);
        
        // Handle custom content
        $this->saveGameContent($game, $request, $em);
        
        $this->addFlash('success', 'Game updated successfully!');
        return $this->redirectToRoute('admin_game_index');
    }
    
    // Load existing content for editing
    $existingContent = null;
    if ($game->getContent()) {
        $existingContent = $game->getContent()->getData();
    }
    
    return $this->render('admin/game/edit.html.twig', [
        'form' => $form,
        'game' => $game,
        'existingContent' => $existingContent,
    ]);
}
```

### saveGameContent() Method
```php
private function saveGameContent(Game $game, Request $request, EntityManagerInterface $em): void
{
    // Get or create GameContent entity
    $content = $game->getContent();
    if (!$content) {
        $content = new GameContent();
        $content->setGame($game);
        $em->persist($content);
    }
    
    // Extract and save content based on game type
    // ... (same logic as create)
}
```

## Use Cases

### 1. Fix Empty Games
**Problem**: Admin created games without content  
**Solution**: Edit game, add content, save

### 2. Update Questions
**Problem**: Questions are outdated or incorrect  
**Solution**: Edit game, modify JSON or regenerate with AI

### 3. Change Difficulty
**Problem**: Questions too easy/hard  
**Solution**: Edit game, regenerate with different difficulty

### 4. Add More Content
**Problem**: Only 3 questions, need 10  
**Solution**: Edit game, generate more questions, merge with existing

### 5. Switch Game Type
**Problem**: Created as PUZZLE, should be TRIVIA  
**Solution**: Edit game, change type, add appropriate content

## Testing Checklist

- [ ] Edit existing TRIVIA game with content
- [ ] Content appears in fields
- [ ] Can modify existing content
- [ ] Can generate new questions
- [ ] Save updates content in database
- [ ] Edit PUZZLE game
- [ ] Word and hint appear
- [ ] Can modify and save
- [ ] Edit MEMORY game
- [ ] Words appear (one per line)
- [ ] Can modify and save
- [ ] Edit ARCADE game
- [ ] Sentences appear
- [ ] Can modify and save
- [ ] Edit game without content
- [ ] Fields are empty
- [ ] Can add content
- [ ] Save creates content

## Files Modified

1. ✅ `templates/admin/game/edit.html.twig`
   - Added AI generator section
   - Added custom content fields
   - Added pre-fill logic
   - Added JavaScript for dynamic sections
   - Added form submission handler

2. ✅ `src/Controller/Admin/Game/GameAdminController.php`
   - Already had `saveGameContent()` method
   - Already passes `existingContent` to template

3. ✅ Cache cleared

## Benefits

✅ **Consistent Experience**: Edit page matches create page  
✅ **Fix Empty Games**: Can add content to existing games  
✅ **Update Content**: Easy to modify existing content  
✅ **AI Integration**: Generate questions while editing  
✅ **Pre-filled Fields**: See existing content immediately  
✅ **Type Safety**: Content fields match game type  

## Known Limitations

1. **No validation**: Can save invalid JSON
2. **No preview**: Can't test game before saving
3. **No history**: Can't see previous versions
4. **No bulk edit**: Must edit games one at a time

## Future Enhancements

1. Add JSON validation before save
2. Add "Preview Game" button
3. Add content version history
4. Add bulk content import/export
5. Add content templates library

---

**Status**: ✅ Complete  
**Date**: February 22, 2026  
**Impact**: All game types can now be edited with custom content
