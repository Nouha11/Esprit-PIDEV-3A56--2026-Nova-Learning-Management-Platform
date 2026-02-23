# Recent Activities System - Implementation Complete

## Overview
A comprehensive activity tracking system that logs and displays user actions across the NOVA platform. Users can see their recent activities on the dashboard and view a complete timeline of all activities.

## Features Implemented

### 1. Activity Tracking Entity
- **Location**: `src/Entity/users/UserActivity.php`
- Tracks all user activities with metadata
- Fields:
  - `user`: Reference to the user
  - `activityType`: Type of activity (game_played, quiz_completed, etc.)
  - `description`: Human-readable description
  - `metadata`: JSON field for additional data (XP, tokens, scores, etc.)
  - `icon`: Bootstrap icon class
  - `color`: Bootstrap color class
  - `createdAt`: Timestamp

### 2. Activity Service
- **Location**: `src/Service/UserActivityService.php`
- Methods:
  - `logActivity()`: Create new activity log
  - `getRecentActivities()`: Get recent activities for a user
  - `getActivitiesByType()`: Filter activities by type
  - `getActivityStats()`: Get activity statistics
  - `cleanupOldActivities()`: Remove old activities (90 days default)
- Auto-assigns icons and colors based on activity type

### 3. Activity Types Supported
- `game_played`: User played a game
- `quiz_completed`: User completed a quiz
- `course_enrolled`: User enrolled in a course
- `reward_claimed`: User claimed a reward
- `level_up`: User leveled up
- `badge_earned`: User earned a badge
- `xp_earned`: User earned experience points
- `tokens_earned`: User earned tokens
- `profile_updated`: User updated their profile
- `login`: User logged in
- `logout`: User logged out
- `password_changed`: User changed password
- `2fa_enabled`: User enabled 2FA
- `2fa_disabled`: User disabled 2FA
- `favorite_added`: User added a favorite
- `favorite_removed`: User removed a favorite

### 4. Dashboard Integration
- **Student Dashboard**: `templates/front/users/student/dashboard.html.twig`
- **Tutor Dashboard**: `templates/front/users/tutor/dashboard.html.twig`
- Shows last 5 recent activities
- Displays activity icon, description, timestamp
- Shows metadata badges (XP, tokens, level)
- "View All" button links to full activity page

### 5. Full Activity Timeline
- **Route**: `/activities`
- **Controller**: `src/Controller/UserActivityController.php`
- **Template**: `templates/user_activity/index.html.twig`
- Features:
  - Activity statistics cards (games played, quizzes completed, XP earned, rewards claimed)
  - Complete activity timeline (last 50 activities)
  - Rich metadata display
  - Activity type badges
  - Responsive design

### 6. Reusable Component
- **Location**: `templates/components/recent_activities.html.twig`
- Can be included anywhere in the application
- Configurable limit and "View All" button

## Database Schema

```sql
CREATE TABLE user_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    metadata JSON DEFAULT NULL,
    icon VARCHAR(50) DEFAULT NULL,
    color VARCHAR(50) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_user_created (user_id, created_at),
    INDEX idx_activity_type (activity_type),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);
```

## Usage Examples

### Logging Activities

```php
// In a controller or service
$this->activityService->logActivity(
    $user,
    'game_played',
    'Played Math Challenge game',
    ['game_name' => 'Math Challenge', 'score' => 850]
);

$this->activityService->logActivity(
    $user,
    'xp_earned',
    'Earned XP from completing a game',
    ['xp' => 50]
);

$this->activityService->logActivity(
    $user,
    'level_up',
    'Leveled up to Level 5!',
    ['level' => 5, 'xp' => 100]
);
```

### Getting Activities

```php
// Get recent activities
$activities = $this->activityService->getRecentActivities($user, 10);

// Get activities by type
$gameActivities = $this->activityService->getActivitiesByType($user, 'game_played', 20);

// Get activity statistics
$stats = $this->activityService->getActivityStats($user);
// Returns: ['game_played' => 5, 'quiz_completed' => 3, ...]
```

### Including in Templates

```twig
{# Dashboard - show last 5 activities #}
{% include 'components/recent_activities.html.twig' with {
    'activities': recentActivities,
    'limit': 5,
    'showViewAll': true
} %}
```

## Activity Metadata Examples

### Game Played
```json
{
    "game_name": "Math Challenge",
    "score": 850,
    "duration": 120
}
```

### Quiz Completed
```json
{
    "quiz_name": "Science Quiz",
    "score": 90,
    "correct_answers": 9,
    "total_questions": 10
}
```

### Level Up
```json
{
    "level": 5,
    "xp": 100,
    "previous_level": 4
}
```

### Reward Claimed
```json
{
    "reward_name": "Premium Course Access",
    "tokens": 50,
    "reward_id": 123
}
```

## Seeding Sample Data

A command is provided to seed sample activities for testing:

```bash
php bin/console app:seed-activities
```

This creates 12 sample activities for each user with different timestamps.

## Cleanup

Old activities can be cleaned up automatically:

```php
// Delete activities older than 90 days
$deletedCount = $this->activityService->cleanupOldActivities(90);
```

You can set up a cron job to run this periodically:

```bash
# Run cleanup daily at 2 AM
0 2 * * * cd /path/to/project && php bin/console app:cleanup-activities
```

## Integration Points

### Where to Log Activities

1. **Game Controller**: Log when games are played, completed, or favorited
2. **Quiz Controller**: Log when quizzes are started, completed, or failed
3. **Course Controller**: Log when courses are enrolled, completed, or dropped
4. **Reward Controller**: Log when rewards are browsed, claimed, or used
5. **Profile Controller**: Log when profiles are updated
6. **Security Controller**: Log logins, logouts, password changes, 2FA changes
7. **Level Service**: Log when users level up or earn badges

### Example Integration

```php
// In GameController after game completion
$this->activityService->logActivity(
    $this->getUser(),
    'game_played',
    "Played {$game->getName()} game",
    [
        'game_name' => $game->getName(),
        'game_id' => $game->getId(),
        'score' => $score,
        'duration' => $duration
    ]
);

// If XP was earned
if ($xpEarned > 0) {
    $this->activityService->logActivity(
        $this->getUser(),
        'xp_earned',
        "Earned {$xpEarned} XP from {$game->getName()}",
        ['xp' => $xpEarned, 'source' => 'game']
    );
}
```

## UI Features

### Dashboard Widget
- Compact view showing last 5 activities
- Icon-based visual representation
- Color-coded by activity type
- Metadata badges for XP, tokens, levels
- Relative timestamps

### Full Timeline Page
- Statistics cards at the top
- Complete activity list (50 most recent)
- Detailed metadata display
- Activity type badges
- Absolute timestamps
- Empty state with call-to-action

## Color Scheme

- **Primary** (Blue): Games, general activities
- **Purple**: Quizzes, challenges
- **Success** (Green): XP earned, level ups, logins, 2FA enabled
- **Warning** (Orange): Tokens, badges, rewards
- **Info** (Cyan): Courses, profile updates
- **Danger** (Red): Favorites, 2FA disabled
- **Secondary** (Gray): Logout, removed items

## Performance Considerations

1. **Indexes**: Created on `user_id + created_at` and `activity_type` for fast queries
2. **Limits**: Dashboard shows only 5 activities, full page shows 50
3. **Cleanup**: Old activities can be deleted to keep table size manageable
4. **Caching**: Consider caching activity stats if needed

## Future Enhancements

1. **Activity Filters**: Filter by type, date range
2. **Activity Search**: Search activities by description
3. **Activity Export**: Export activities to CSV/PDF
4. **Activity Notifications**: Notify users of important activities
5. **Activity Sharing**: Share achievements on social media
6. **Activity Insights**: Weekly/monthly activity reports
7. **Activity Comparison**: Compare activity with other users
8. **Activity Achievements**: Unlock achievements based on activity patterns

## Files Created/Modified

### New Files
- `src/Entity/users/UserActivity.php`
- `src/Repository/UserActivityRepository.php`
- `src/Service/UserActivityService.php`
- `src/Controller/UserActivityController.php`
- `src/Command/SeedActivitiesCommand.php`
- `templates/components/recent_activities.html.twig`
- `templates/user_activity/index.html.twig`
- `migrations/Version20260222125830.php`

### Modified Files
- `src/Controller/Front/users/StudentController.php`
- `src/Controller/Front/users/TutorController.php`
- `templates/front/users/student/dashboard.html.twig`
- `templates/front/users/tutor/dashboard.html.twig`

## Testing

1. **View Dashboard**: Navigate to student or tutor dashboard to see recent activities widget
2. **View All Activities**: Click "View All" button to see complete timeline
3. **Check Statistics**: Verify activity counts in statistics cards
4. **Test Empty State**: Create a new user to see empty state message
5. **Test Metadata**: Verify XP, tokens, and level badges display correctly

## Status

✅ **COMPLETE** - Recent Activities system is fully functional with dashboard integration and full timeline view.
