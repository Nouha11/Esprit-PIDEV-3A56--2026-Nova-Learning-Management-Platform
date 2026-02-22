# Game Content Customization System

## Overview
This system allows admins to customize game content without modifying templates. Game-specific data is stored in the `GameContent` entity with a JSON field.

## Database Setup

### 1. Run the SQL Migration
Execute the SQL file to create the `game_content` table:
```bash
mysql -u root nova_db < create_game_content_table.sql
```

Or run manually in phpMyAdmin/MySQL:
```sql
CREATE TABLE IF NOT EXISTS game_content (
    id INT AUTO_INCREMENT NOT NULL, 
    game_id INT NOT NULL, 
    data JSON DEFAULT NULL, 
    UNIQUE INDEX UNIQ_6B074F86E48FD905 (game_id), 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

ALTER TABLE game_content 
ADD CONSTRAINT FK_6B074F86E48FD905 
FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE;
```

## Entities Created

### GameContent Entity
- **Location**: `src/Entity/Gamification/GameContent.php`
- **Purpose**: Stores game-specific content in JSON format
- **Relationship**: One-to-One with Game entity

### Fields by Game Type

#### PUZZLE (Word Scramble)
```php
$content->setWord('SYMFONY');
$content->setHint('A PHP framework');
```

#### MEMORY (Card Flip)
```php
$content->setWords(['🍎', '🍌', '🍇', '🍊', '🍓', '🍉']);
```

#### TRIVIA (Quiz)
```php
$content->setTopic('World History');
$content->setQuestions([
    [
        'question' => 'What year did World War II end?',
        'choices' => ['1943', '1944', '1945', '1946'],
        'correct' => 2  // Index of correct answer
    ],
    // ... more questions
]);
```

#### ARCADE (Typing Challenge)
```php
$content->setSentences([
    'The quick brown fox jumps over the lazy dog.',
    'Practice makes perfect.',
    'Typing speed improves with time.'
]);
```

## Next Steps

### 1. Update GameFormType
Add dynamic form fields based on game type:

```php
// In src/Form/Admin/gamification/GameFormType.php
use App\Entity\Gamification\GameContent;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

public function buildForm(FormBuilderInterface $builder, array $options): void
{
    // ... existing fields ...
    
    // Add content fields based on game type
    $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
        $game = $event->getData();
        $form = $event->getForm();
        
        if ($game && $game->getType()) {
            $this->addContentFields($form, $game->getType());
        }
    });
    
    $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();
        
        if (isset($data['type'])) {
            $this->addContentFields($form, $data['type']);
        }
    });
}

private function addContentFields(FormInterface $form, string $gameType): void
{
    switch ($gameType) {
        case 'PUZZLE':
            $form->add('content_word', TextType::class, [
                'label' => 'Word to Scramble',
                'required' => false,
                'mapped' => false,
                'attr' => ['placeholder' => 'e.g., SYMFONY']
            ]);
            $form->add('content_hint', TextType::class, [
                'label' => 'Hint',
                'required' => false,
                'mapped' => false,
                'attr' => ['placeholder' => 'e.g., A PHP framework']
            ]);
            break;
            
        case 'MEMORY':
            $form->add('content_words', TextareaType::class, [
                'label' => 'Words/Emojis (one per line, 6 items)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'rows' => 6,
                    'placeholder' => "🍎\n🍌\n🍇\n🍊\n🍓\n🍉"
                ]
            ]);
            break;
            
        case 'TRIVIA':
            $form->add('content_topic', TextType::class, [
                'label' => 'Topic',
                'required' => false,
                'mapped' => false,
                'attr' => ['placeholder' => 'e.g., World History']
            ]);
            // Add collection for questions (complex, see below)
            break;
            
        case 'ARCADE':
            $form->add('content_sentences', TextareaType::class, [
                'label' => 'Sentences (one per line, 3-5 items)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => "The quick brown fox jumps over the lazy dog.\nPractice makes perfect."
                ]
            ]);
            break;
    }
}
```

### 2. Update GameAdminController
Handle content data when creating/editing games:

```php
// In new() and edit() methods
if ($form->isSubmitted() && $form->isValid()) {
    $game = $form->getData();
    
    // Create or update GameContent
    $content = $game->getContent() ?? new GameContent();
    $content->setGame($game);
    
    // Extract content from form
    $formData = $form->getData();
    $gameType = $game->getType();
    
    switch ($gameType) {
        case 'PUZZLE':
            $content->setWord($form->get('content_word')->getData());
            $content->setHint($form->get('content_hint')->getData());
            break;
            
        case 'MEMORY':
            $wordsText = $form->get('content_words')->getData();
            $words = array_filter(array_map('trim', explode("\n", $wordsText)));
            $content->setWords($words);
            break;
            
        case 'TRIVIA':
            $content->setTopic($form->get('content_topic')->getData());
            // Handle questions collection
            break;
            
        case 'ARCADE':
            $sentencesText = $form->get('content_sentences')->getData();
            $sentences = array_filter(array_map('trim', explode("\n", $sentencesText)));
            $content->setSentences($sentences);
            break;
    }
    
    $game->setContent($content);
    $entityManager->persist($game);
    $entityManager->flush();
}
```

### 3. Update Game Engines to Use Content

#### Word Scramble (word-scramble.js)
```javascript
const settings = JSON.parse(container.dataset.settings || '{}');
const word = settings.word || 'SYMFONY';
const hint = settings.hint || 'Guess the word';
```

#### Memory Match (memory-match.js)
```javascript
const words = settings.words || ['🍎', '🍌', '🍇', '🍊', '🍓', '🍉'];
```

#### Quick Quiz (quick-quiz.js)
```javascript
const questions = settings.questions || generateDefaultQuestions(settings.difficulty);
```

#### Typing Challenge (Create new: typing-challenge.js)
```javascript
const sentences = settings.sentences || ['The quick brown fox jumps over the lazy dog.'];
```

### 4. Update GameController::buildGameSettings()
Pass content data to game engines:

```php
private function buildGameSettings(Game $game): array
{
    $settings = [
        'difficulty' => $game->getDifficulty(),
        'timeLimit' => $this->calculateTimeLimit($game),
    ];
    
    // Add content-specific settings
    $content = $game->getContent();
    if ($content) {
        switch ($game->getType()) {
            case 'PUZZLE':
                $settings['word'] = $content->getWord();
                $settings['hint'] = $content->getHint();
                break;
                
            case 'MEMORY':
                $settings['words'] = $content->getWords();
                break;
                
            case 'TRIVIA':
                $settings['questions'] = $content->getQuestions();
                $settings['topic'] = $content->getTopic();
                break;
                
            case 'ARCADE':
                $settings['sentences'] = $content->getSentences();
                break;
        }
    }
    
    return $settings;
}
```

### 5. Add JavaScript to Game Form Template
Add dynamic form field visibility:

```javascript
// In templates/admin/game/new.html.twig and edit.html.twig
document.getElementById('game_form_type').addEventListener('change', function() {
    const gameType = this.value;
    
    // Hide all content fields
    document.querySelectorAll('[id^="game_form_content_"]').forEach(field => {
        field.closest('.mb-3').style.display = 'none';
    });
    
    // Show relevant fields
    switch(gameType) {
        case 'PUZZLE':
            showFields(['content_word', 'content_hint']);
            break;
        case 'MEMORY':
            showFields(['content_words']);
            break;
        case 'TRIVIA':
            showFields(['content_topic', 'content_questions']);
            break;
        case 'ARCADE':
            showFields(['content_sentences']);
            break;
    }
});

function showFields(fieldNames) {
    fieldNames.forEach(name => {
        const field = document.getElementById('game_form_' + name);
        if (field) {
            field.closest('.mb-3').style.display = 'block';
        }
    });
}
```

## Benefits

1. **No Template Changes**: Game templates remain generic
2. **Flexible Content**: Each game type has custom fields
3. **Easy Maintenance**: Content stored in database, not code
4. **Scalable**: Easy to add new game types
5. **Admin-Friendly**: Simple form fields for content entry

## Testing

1. Create a PUZZLE game with word "SYMFONY" and hint "A PHP framework"
2. Create a MEMORY game with 6 emojis
3. Create a TRIVIA game with 5 questions
4. Create an ARCADE game with 3 sentences
5. Play each game and verify content appears correctly

## Future Enhancements

1. **AI Question Generator**: Add button to generate trivia questions using Hugging Face API
2. **Content Validation**: Ensure minimum/maximum items for each type
3. **Content Preview**: Show preview of game before saving
4. **Content Templates**: Pre-defined content sets for quick game creation
5. **Import/Export**: Share game content between instances
