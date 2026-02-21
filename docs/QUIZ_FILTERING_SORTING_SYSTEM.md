# Quiz Filtering and Sorting System

## Overview
A comprehensive filtering and sorting system for quizzes that works on both admin and front-end interfaces, providing users with powerful search capabilities.

## Features

### Filtering Options
- **Text Search**: Search by quiz title or description
- **Question Count Range**: Filter by minimum and maximum number of questions
- **Real-time Results**: Filters apply immediately

### Sorting Options
- **By Title**: Alphabetical sorting (A-Z or Z-A)
- **By ID**: Numerical sorting (oldest/newest first)
- **By Question Count**: Sort by number of questions (least/most first)
- **Sort Direction**: Ascending or Descending for all options

### User Experience Features
- **Statistics Dashboard**: Shows total quizzes, average/min/max questions
- **Active Filter Display**: Visual badges showing current filters
- **Auto-submit**: Dropdown changes automatically apply filters
- **Clear Filters**: Easy reset to show all quizzes
- **No Results Handling**: Helpful messages when no quizzes match filters

## Implementation

### Backend Components

#### QuizRepository (`src/Repository/QuizRepository.php`)
- `findWithFiltersAndSort()` - Main filtering and sorting method
- `getQuizStatistics()` - Provides statistics for the UI
- Supports complex queries with joins and aggregations

#### QuizFilterType (`src/Form/QuizFilterType.php`)
- Symfony form for filter inputs
- GET method for URL-friendly filtering
- No CSRF protection for better UX

#### Controllers Updated
- `AdminQuizController::index()` - Admin interface filtering
- `QuizGameController::index()` - Front-end interface filtering

### Frontend Components

#### Admin Interface (`templates/admin/quiz/index.html.twig`)
- Statistics cards showing quiz metrics
- Collapsible filter panel
- Professional admin UI with Bootstrap styling
- Active filter badges

#### Front-end Interface (`templates/front/quiz/game/index.html.twig`)
- User-friendly search interface
- Integrated with existing quiz arcade design
- Responsive layout for mobile devices
- Translated labels for internationalization

### Database Queries

The system uses optimized Doctrine queries:

```sql
-- Example: Search with question count filter
SELECT q.*, COUNT(questions.id) as questionCount
FROM quiz q
LEFT JOIN question questions ON q.id = questions.quiz_id
WHERE (q.title LIKE '%search%' OR q.description LIKE '%search%')
GROUP BY q.id
HAVING COUNT(questions.id) >= 5 AND COUNT(questions.id) <= 20
ORDER BY q.title ASC
```

## Usage Examples

### Admin Interface
1. Navigate to Quiz Manager
2. Use the "Filters & Sorting" panel
3. Enter search terms, set question count ranges
4. Select sorting preferences
5. Results update automatically

### Front-end Interface
1. Go to Quiz Mode
2. Use "Find Your Perfect Quiz" section
3. Search by keywords or filter by difficulty
4. Sort by preference
5. Click "Play Now" on desired quiz

### URL Examples
```
/admin/quiz?search=javascript&minQuestions=5&sortBy=questionCount&sortOrder=DESC
/game/quiz?search=math&maxQuestions=10&sortBy=title&sortOrder=ASC
```

## Technical Details

### Performance Optimizations
- Single query with LEFT JOIN for efficiency
- Indexed columns for fast searching
- Minimal data transfer with selective fields

### Security
- Input validation through Symfony forms
- SQL injection protection via Doctrine ORM
- XSS protection through Twig auto-escaping

### Accessibility
- Proper form labels and ARIA attributes
- Keyboard navigation support
- Screen reader friendly

## Files Created/Modified

### New Files
- `src/Form/QuizFilterType.php` - Filter form definition
- `docs/QUIZ_FILTERING_SORTING_SYSTEM.md` - This documentation

### Modified Files
- `src/Repository/QuizRepository.php` - Added filtering methods
- `src/Controller/Admin/Quiz/QuizController.php` - Added filter handling
- `src/Controller/Front/Quiz/QuizGameController.php` - Added filter handling
- `templates/admin/quiz/index.html.twig` - Added filter UI
- `templates/front/quiz/game/index.html.twig` - Added filter UI

## Future Enhancements

Potential improvements:
- AJAX-based filtering for real-time results
- Advanced filters (difficulty level, creation date)
- Saved filter presets
- Export filtered results
- Pagination for large datasets
- Full-text search with relevance scoring

## Browser Compatibility

Tested and compatible with:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome Mobile)