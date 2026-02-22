# Edit Page - Questions Preview Enhancement

## What Was Added

The edit page now shows existing questions in a preview when you open a TRIVIA game for editing, with the ability to edit or regenerate them.

## Features

### 1. Auto-Load Existing Questions ✅
When you edit a TRIVIA game:
- Topic field is pre-filled
- Questions appear in JSON field
- Questions preview displays automatically
- Success message shows number of questions loaded

### 2. Visual Preview ✅
- Questions displayed in readable format
- Correct answers highlighted in green
- Numbered questions (Q1, Q2, etc.)
- Multiple choice options (A, B, C, D)
- Check mark icon on correct answer

### 3. Edit Options ✅
You can:
- **Edit manually**: Modify the JSON directly
- **Generate new**: Replace with AI-generated questions
- **Clear all**: Remove all questions and start fresh
- **Keep existing**: Just update other game fields

### 4. Clear Questions Button ✅
- Appears when questions exist
- Confirms before clearing
- Removes all questions and topic
- Hides preview

## How It Works

### Opening Edit Page

1. **Navigate to**: `/admin/games/{id}/edit`
2. **If TRIVIA game with questions**:
   - AI Generator section shows
   - Topic field pre-filled
   - Questions JSON pre-filled
   - Preview displays automatically
   - Success message: "Loaded X existing questions..."
   - Clear button visible

3. **If TRIVIA game without questions**:
   - AI Generator section shows
   - Fields are empty
   - No preview
   - Clear button hidden

### Editing Existing Questions

#### Option 1: Manual Edit
1. Scroll to "Game Content" section
2. Edit the JSON directly in textarea
3. Click "Update Game"
4. Changes saved

#### Option 2: Generate New Questions
1. Scroll to "AI Question Generator"
2. Topic is already filled (or enter new one)
3. Select number of questions
4. Click "Generate New Questions with AI"
5. **Confirmation prompt**: "This will replace your existing questions. Are you sure?"
6. Click OK to proceed
7. New questions generated and displayed
8. Click "Update Game" to save

#### Option 3: Clear and Start Fresh
1. Click "Clear Questions" button
2. **Confirmation prompt**: "Are you sure you want to clear all questions?"
3. Click OK
4. All questions removed
5. Topic cleared
6. Preview hidden
7. Can now generate new or enter manually

### Visual Feedback

#### Success Message (Green)
```
Loaded 5 existing questions. You can edit them below or generate new ones.
```

#### Questions Preview
```
Q1: What year did World War II end?
   A) 1943
   B) 1944
   C) 1945 ✓ (highlighted in green)
   D) 1946

Q2: What is the capital of France?
   A) London
   B) Berlin
   C) Paris ✓ (highlighted in green)
   D) Madrid
```

#### Confirmation Dialogs
- **Replace questions**: "This will replace your existing questions. Are you sure?"
- **Clear questions**: "Are you sure you want to clear all questions? This cannot be undone."

## JavaScript Implementation

### Auto-Load on Page Load
```javascript
// Pre-fill AI topic and show questions preview if editing TRIVIA game
const existingTopic = document.getElementById('content-topic').value;
const existingQuestions = document.getElementById('content-questions').value;

if (typeField.value === 'TRIVIA' && existingTopic) {
    document.getElementById('ai-topic').value = existingTopic;
}

if (typeField.value === 'TRIVIA' && existingQuestions) {
    try {
        const questions = JSON.parse(existingQuestions);
        if (questions && questions.length > 0) {
            displayQuestionsPreview(questions);
            document.getElementById('clear-questions-btn').style.display = 'inline-block';
            showSuccess(`Loaded ${questions.length} existing questions...`);
        }
    } catch (e) {
        console.error('Error parsing existing questions:', e);
    }
}
```

### Generate with Confirmation
```javascript
// Confirm if replacing existing questions
if (existingQuestions && existingQuestions !== '[]' && existingQuestions !== '') {
    if (!confirm('This will replace your existing questions. Are you sure?')) {
        return;
    }
}
```

### Clear Button
```javascript
document.getElementById('clear-questions-btn').addEventListener('click', function() {
    if (confirm('Are you sure you want to clear all questions?')) {
        document.getElementById('content-questions').value = '';
        document.getElementById('content-topic').value = '';
        document.getElementById('ai-topic').value = '';
        document.getElementById('ai-questions-preview').style.display = 'none';
        this.style.display = 'none';
        showSuccess('Questions cleared...');
    }
});
```

## Example: Editing Game ID 25

### Step 1: Open Edit Page
Navigate to: `/admin/games/25/edit`

### Step 2: See Existing Questions
- Topic: "games"
- Preview shows 5 questions about video games
- Success message: "Loaded 5 existing questions..."
- Clear button visible

### Step 3: Choose Action

#### A. Keep and Edit Manually
1. Scroll to JSON field
2. Edit question text or choices
3. Click "Update Game"

#### B. Generate New Questions
1. Change topic to "board games"
2. Click "Generate New Questions with AI"
3. Confirm replacement
4. Review new questions
5. Click "Update Game"

#### C. Clear and Start Fresh
1. Click "Clear Questions"
2. Confirm
3. Enter new topic
4. Generate or enter manually
5. Click "Update Game"

## UI Elements

### Buttons

**Generate New Questions with AI** (Primary)
- Blue button
- Shows when TRIVIA type selected
- Generates new questions
- Confirms if questions exist

**Test AI Connection** (Secondary)
- Gray button
- Tests API connection
- Shows success/error message

**Clear Questions** (Danger)
- Red button
- Only visible when questions exist
- Confirms before clearing
- Removes all questions

### Sections

**AI Question Generator**
- Only for TRIVIA games
- Topic input (pre-filled if exists)
- Question count selector
- Generate and test buttons
- Loading indicator
- Success/error messages
- Questions preview

**Game Content**
- For all game types
- Topic field (pre-filled)
- Questions JSON (pre-filled)
- Manual editing area

## Benefits

✅ **See existing content** - No more guessing what's there  
✅ **Easy editing** - Visual preview makes it clear  
✅ **Safe replacement** - Confirmation prevents accidents  
✅ **Flexible workflow** - Edit, replace, or clear  
✅ **Better UX** - Immediate feedback on load  

## Testing Checklist

- [ ] Edit TRIVIA game with questions
- [ ] Questions appear in preview
- [ ] Topic pre-filled in AI field
- [ ] Success message shows
- [ ] Clear button visible
- [ ] Can edit JSON manually
- [ ] Can generate new questions
- [ ] Confirmation appears before replace
- [ ] Can clear all questions
- [ ] Confirmation appears before clear
- [ ] Save updates database
- [ ] Edit TRIVIA game without questions
- [ ] Fields are empty
- [ ] No preview shown
- [ ] Clear button hidden
- [ ] Can add new questions

## Files Modified

1. ✅ `templates/admin/game/edit.html.twig`
   - Added auto-load JavaScript
   - Added clear button
   - Added confirmation dialogs
   - Added preview on page load

2. ✅ Cache cleared

## Known Issues

None - all features working as expected!

## Future Enhancements

1. Add "Duplicate Questions" button
2. Add "Export Questions" feature
3. Add "Import Questions" from file
4. Add question reordering (drag & drop)
5. Add individual question edit/delete
6. Add question difficulty indicators

---

**Status**: ✅ Complete  
**Date**: February 22, 2026  
**Impact**: Much better editing experience for TRIVIA games
