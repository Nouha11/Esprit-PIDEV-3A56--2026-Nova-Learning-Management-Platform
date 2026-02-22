# Game Rating System - Complete Implementation

## Overview
Implemented a complete 1-5 star rating system for games with Ajax submission, average rating display, and dark/light theme support.

## Database Schema

### GameRating Entity
```sql
CREATE TABLE game_rating (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL,
    UNIQUE KEY unique_user_game_rating (game_id, user_id),
    CONSTRAINT fk_rating_game FOREIGN KEY (game_id) REFERENCES game(id) ON DELETE CASCADE,
    CONSTRAINT fk_rating_user FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);
```

**Key Features**:
- Unique constraint on (game_id, user_id) - each user can rate a game only once
- Rating validation: 1-5 stars
- Cascade delete when game or user is deleted
- Timestamps for created_at and updated_at

## Files Created

### 1. Entity
- `src/Entity/Gamification/GameRating.php`
  - Fields: id, game, user, rating, createdAt, updatedAt
  - Validation: rating must be 1-5
  - Unique constraint enforced

### 2. Repository
- `src/Repository/Gamification/GameRatingRepository.php`
  - `getGameRatingStats()` - Get average and count for a game
  - `getUserRating()` - Get user's rating for a game
  - `getAverageRatingsForGames()` - Bulk fetch ratings for game list
  - `saveRating()` - Create or update rating

### 3. Controller
- `src/Controller/Front/Game/GameRatingController.php`
  - `POST /games/{id}/rate` - Submit/update rating (Ajax)
  - `GET /games/{id}/rating-stats` - Get rating stats (Ajax)

### 4. Templates
- `templates/front/game/_star_rating.html.twig` - Display component
- `templates/front/game/_star_rating_input.html.twig` - Interactive input component

### 5. Database
- `database_seeds/create_game_rating_table.sql` - SQL script
- `src/Command/CreateGameRatingTableCommand.php` - Setup command

## Controller Implementation

### Submit Rating Endpoint
```php
#[Route('/{id}/rate', name: 'front_game_rate', methods: ['POST'])]
public function rateGame(Game $game, Request $request): JsonResponse
{
    $user = $this->getUser();
    $data = json_decode($request->getContent(), true);
    $ratingValue = $data['rating'] ?? null;

    // Validate rating (1-5)
    if ($ratingValue === null || $ratingValue < 1 || $ratingValue > 5) {
        return $this->json(['success' => false, 'message' => 'Invalid rating'], 400);
    }

    // Check if user already rated
    $existingRating = $this->ratingRepository->getUserRating($game, $user);

    if ($existingRating) {
        // Update existing rating
        $existingRating->setRating($ratingValue);
        $message = 'Your rating has been updated!';
    } else {
        // Create new rating
        $existingRating = new GameRating();
        $existingRating->setGame($game);
        $existingRating->setUser($user);
        $existingRating->setRating($ratingValue);
        $message = 'Thank you for rating this game!';
    }

    $this->ratingRepository->saveRating($existingRating);
    $stats = $this->ratingRepository->getGameRatingStats($game);

    return $this->json([
        'success' => true,
        'message' => $message,
        'userRating' => $ratingValue,
        'averageRating' => $stats['average'],
        'totalRatings' => $stats['count']
    ]);
}
```

## Doctrine Queries

### Calculate Average Rating
```php
public function getGameRatingStats(Game $game): array
{
    $result = $this->createQueryBuilder('gr')
        ->select('AVG(gr.rating) as averageRating', 'COUNT(gr.id) as totalRatings')
        ->where('gr.game = :game')
        ->setParameter('game', $game)
        ->getQuery()
        ->getSingleResult();

    return [
        'average' => $result['averageRating'] ? round((float)$result['averageRating'], 1) : 0,
        'count' => (int)$result['totalRatings']
    ];
}
```

### Bulk Fetch Ratings for Game List
```php
public function getAverageRatingsForGames(array $gameIds): array
{
    $results = $this->createQueryBuilder('gr')
        ->select('IDENTITY(gr.game) as gameId', 'AVG(gr.rating) as averageRating', 'COUNT(gr.id) as totalRatings')
        ->where('gr.game IN (:gameIds)')
        ->setParameter('gameIds', $gameIds)
        ->groupBy('gr.game')
        ->getQuery()
        ->getResult();

    $ratings = [];
    foreach ($results as $result) {
        $ratings[$result['gameId']] = [
            'average' => round((float)$result['averageRating'], 1),
            'count' => (int)$result['totalRatings']
        ];
    }

    return $ratings;
}
```

## Twig Components

### Display Component (`_star_rating.html.twig`)
```twig
{# Usage #}
{% include 'front/game/_star_rating.html.twig' with {
    'rating': 4.5,
    'count': 23,
    'size': 'md',
    'showCount': true
} %}
```

**Features**:
- Shows filled, half-filled, and empty stars
- Displays average rating and count
- Sizes: sm, md, lg
- Dark/light theme support

### Interactive Input Component (`_star_rating_input.html.twig`)
```twig
{# Usage #}
{% include 'front/game/_star_rating_input.html.twig' with {
    'gameId': game.id,
    'userRating': 4,
    'averageRating': 4.2,
    'totalRatings': 15
} %}
```

**Features**:
- Hover effect to preview rating
- Click to submit rating
- Ajax submission (no page reload)
- Updates existing rating if user already rated
- Shows current user rating
- Displays average rating and count
- Toast notifications
- Dark/light theme support

## JavaScript Implementation

### Star Rating Input
```javascript
// Hover effect
stars.forEach((star, index) => {
    star.addEventListener('mouseenter', function() {
        highlightStars(index + 1);
    });
});

// Click to rate
stars.forEach((star, index) => {
    star.addEventListener('click', function() {
        const rating = index + 1;
        submitRating(rating);
    });
});

// Submit via Ajax
function submitRating(rating) {
    fetch(`/games/${gameId}/rate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ rating: rating })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI with new average and count
            document.getElementById('avg-rating-' + gameId).textContent = data.averageRating;
            document.getElementById('total-ratings-' + gameId).textContent = data.totalRatings;
            showToast(data.message, 'success');
        }
    });
}
```

## Integration Points

### 1. Game Show Page
- Rating input component displayed after game details
- Shows user's current rating if they've rated before
- Displays average rating and total count
- Non-logged-in users see display-only component

### 2. Game List Page
- Each game card shows average rating
- Small star display with count
- Ratings fetched in bulk for performance

### 3. Game Completion
- After completing a game, users can rate it
- Rating modal/section appears
- Encourages user engagement

## Dark/Light Theme Support

### CSS Variables
```css
/* Light theme */
.star-icon {
    color: var(--bs-secondary);
}

.star-icon.bi-star-fill {
    color: #ffc107;
}

/* Dark theme */
[data-bs-theme="dark"] .star-icon {
    color: rgba(255, 255, 255, 0.3);
}

[data-bs-theme="dark"] .star-icon.bi-star-fill {
    color: #ffc107 !important;
}
```

## Setup Instructions

### 1. Create Database Table
```bash
# Option 1: Using command
php bin/console app:create-game-rating-table

# Option 2: Run SQL manually
# Execute: database_seeds/create_game_rating_table.sql
```

### 2. Clear Cache
```bash
php bin/console cache:clear
```

### 3. Test the Feature
1. Visit a game page: `http://127.0.0.1:8001/games/{id}`
2. Scroll to the "Game Rating" section
3. Click on a star to rate (1-5)
4. See toast notification confirming rating
5. Average rating updates immediately
6. Try rating again - it updates your existing rating

## Features

### ✅ Unique Ratings
- Database constraint ensures one rating per user per game
- Updating a rating modifies the existing record
- No duplicate ratings possible

### ✅ Ajax Submission
- No page reload required
- Instant feedback with toast notifications
- Real-time average rating updates
- Smooth user experience

### ✅ Visual Feedback
- Hover effect shows preview
- Filled/half/empty stars for average display
- Color-coded (gold for filled, muted for empty)
- Responsive sizing (sm, md, lg)

### ✅ Performance
- Bulk fetching for game lists
- Indexed database columns
- Efficient queries with AVG() and COUNT()
- Minimal overhead

### ✅ Theme Support
- Works in both light and dark modes
- Proper contrast in all themes
- Bootstrap Icons integration
- CSS variables for easy customization

## Testing Checklist

- [ ] Create a rating for a game
- [ ] Update an existing rating
- [ ] View average rating on game card
- [ ] View average rating on game detail page
- [ ] Hover over stars shows preview
- [ ] Click star submits rating via Ajax
- [ ] Toast notification appears
- [ ] Average rating updates immediately
- [ ] Rating count increments
- [ ] Non-logged-in users see display only
- [ ] Dark mode styling works correctly
- [ ] Light mode styling works correctly
- [ ] Cannot rate same game twice (updates instead)
- [ ] Rating persists after page reload

## Database Queries for Testing

```sql
-- View all ratings
SELECT u.username, g.name, gr.rating, gr.created_at
FROM game_rating gr
JOIN user u ON gr.user_id = u.id
JOIN game g ON gr.game_id = g.id
ORDER BY gr.created_at DESC;

-- Get average rating for a game
SELECT g.name, AVG(gr.rating) as avg_rating, COUNT(gr.id) as total_ratings
FROM game g
LEFT JOIN game_rating gr ON g.id = gr.game_id
WHERE g.id = 1
GROUP BY g.id;

-- Get all games with their ratings
SELECT g.name, 
       ROUND(AVG(gr.rating), 1) as avg_rating, 
       COUNT(gr.id) as total_ratings
FROM game g
LEFT JOIN game_rating gr ON g.id = gr.game_id
GROUP BY g.id
ORDER BY avg_rating DESC;

-- Add a test rating
INSERT INTO game_rating (game_id, user_id, rating, created_at)
VALUES (1, 1, 5, NOW());
```

## Status
✅ **COMPLETE** - Game rating system fully implemented and ready for use!
