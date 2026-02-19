# Leveling System & Leaderboard

## Overview
This document describes the leveling algorithm and leaderboard feature for the gamification module.

## Leveling System

### Level Thresholds

| Level | Name | XP Range | Badge Color | Icon |
|-------|------|----------|-------------|------|
| 1 | Novice | 0-99 | Secondary (Gray) | Star |
| 2 | Apprentice | 100-249 | Info (Blue) | Star Fill |
| 3 | Adept | 250-499 | Primary (Purple) | Trophy |
| 4 | Expert | 500-999 | Warning (Yellow) | Trophy Fill |
| 5 | Master | 1000+ | Success (Green) | Gem |

### LevelCalculatorService

**Location**: `src/Service/game/LevelCalculatorService.php`

**Main Method**: `calculateLevel(int $xp): array`

**Returns**:
```php
[
    'level' => 3,                    // Current level number
    'name' => 'Adept',              // Level name
    'progress' => 45.5,             // Progress to next level (%)
    'currentLevelMin' => 250,       // Min XP for current level
    'nextLevelMin' => 500,          // Min XP for next level
    'xpInCurrentLevel' => 114,      // XP earned in current level
    'xpNeededForNextLevel' => 136   // XP needed to level up
]
```

**Usage Example**:
```php
$levelCalculator = $this->container->get(LevelCalculatorService::class);
$levelInfo = $levelCalculator->calculateLevel(364);
// Returns: Level 3 (Adept), 45.5% progress
```

### Helper Methods

- `getLevelName(int $level): string` - Get level name
- `getLevelBadgeColor(int $level): string` - Get Bootstrap color
- `getLevelIcon(int $level): string` - Get Bootstrap icon
- `getXpForLevel(int $level): int` - Get min XP for level
- `getLevelThresholds(): array` - Get all thresholds

## Leaderboard Feature

### Routes

1. **Leaderboard Page**: `/leaderboard`
   - Route name: `front_leaderboard_index`
   - Method: GET
   - Access: Public

2. **Leaderboard Data API**: `/leaderboard/data`
   - Route name: `front_leaderboard_data`
   - Method: GET
   - Parameters: `?search=name`
   - Returns: JSON

3. **My Rank API**: `/leaderboard/my-rank`
   - Route name: `front_leaderboard_my_rank`
   - Method: GET
   - Access: ROLE_STUDENT
   - Returns: JSON

### Features

✅ Real-time Ajax search (no page reload)
✅ Debounced search (300ms delay)
✅ Rank by XP (primary), then tokens (secondary)
✅ Top 3 players highlighted with medals
✅ Level badges with progress bars
✅ Current user's rank card (for logged-in students)
✅ Responsive design
✅ Dark mode support


### API Response Examples

**Leaderboard Data** (`/leaderboard/data`):
```json
{
  "success": true,
  "total": 4,
  "data": [
    {
      "rank": 1,
      "id": 5,
      "firstName": "Nouha",
      "lastName": "Hamrouni",
      "fullName": "Nouha Hamrouni",
      "xp": 270,
      "tokens": 80,
      "level": 3,
      "levelName": "Adept",
      "progress": 8.0,
      "badgeColor": "primary",
      "icon": "bi-trophy"
    }
  ]
}
```

**My Rank** (`/leaderboard/my-rank`):
```json
{
  "success": true,
  "data": {
    "rank": 1,
    "totalPlayers": 4,
    "xp": 270,
    "tokens": 80,
    "level": 3,
    "levelName": "Adept",
    "progress": 8.0
  }
}
```

### UI Components

**Rank Badges**:
- 🥇 Rank 1: Gold gradient with trophy icon
- 🥈 Rank 2: Silver gradient with trophy icon
- 🥉 Rank 3: Bronze gradient with trophy icon
- Others: Gray background with rank number

**Level Badges**:
- Color-coded by level
- Icon representing level tier
- Progress bar showing % to next level

**Search Bar**:
- Real-time filtering
- Debounced (300ms)
- Clear button
- Auto-focus on page load

### Testing

**Test the Leveling Algorithm**:
```php
// In a controller or command
$levelCalculator = $this->container->get(LevelCalculatorService::class);

// Test different XP values
$tests = [0, 50, 100, 250, 500, 1000, 2000];
foreach ($tests as $xp) {
    $result = $levelCalculator->calculateLevel($xp);
    dump("XP: $xp => Level {$result['level']} ({$result['name']}), Progress: {$result['progress']}%");
}
```

**Test the Leaderboard**:
1. Go to `/leaderboard`
2. Verify all students are listed
3. Check ranking order (highest XP first)
4. Test search functionality
5. Verify your rank card appears (if logged in as student)

### Integration with Existing Code

The leveling system can be integrated anywhere you display student XP:

```twig
{# In any Twig template #}
{% set levelInfo = level_calculator.calculateLevel(student.totalXP) %}

<div class="level-badge">
    <span class="badge bg-{{ level_calculator.getLevelBadgeColor(levelInfo.level) }}">
        <i class="{{ level_calculator.getLevelIcon(levelInfo.level) }}"></i>
        {{ levelInfo.name }}
    </span>
    <div class="progress">
        <div class="progress-bar" style="width: {{ levelInfo.progress }}%"></div>
    </div>
</div>
```

### Future Enhancements

- [ ] Level-up notifications
- [ ] Rewards for reaching new levels
- [ ] Weekly/monthly leaderboards
- [ ] Category-specific leaderboards (by game type)
- [ ] Friend leaderboards
- [ ] Achievement badges for top ranks
- [ ] Export leaderboard to PDF/CSV
- [ ] Historical rank tracking
- [ ] Level-based perks (unlock features at certain levels)

### Troubleshooting

**Leaderboard not loading**:
- Check browser console for errors
- Verify routes are accessible
- Check database has student records

**Search not working**:
- Verify JavaScript is enabled
- Check network tab for API calls
- Ensure search parameter is passed correctly

**Incorrect levels**:
- Verify XP values in database
- Check level threshold constants
- Test with known XP values

### Related Files

- `src/Service/game/LevelCalculatorService.php` - Leveling logic
- `src/Controller/Front/Game/LeaderboardController.php` - Leaderboard routes
- `templates/front/game/leaderboard.html.twig` - Leaderboard UI
- `src/Entity/users/StudentProfile.php` - Student XP/tokens
