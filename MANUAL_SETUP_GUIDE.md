# Manual Setup Guide - Game Reward System

## Overview
This guide will help you manually set up the game reward system through the admin interface.

## Step 1: Access Admin Panel

1. Login as an admin user
2. Navigate to `/admin` or click "Admin Panel" in the navigation

## Step 2: Create Milestone Badges

Navigate to **Admin Panel → Rewards** or go to `/admin/rewards`

### Recommended Badges to Create

#### 1. First Victory Badge 🏆
- **Name**: First Victory
- **Description**: Awarded for winning your first game
- **Type**: BADGE
- **Value**: 0
- **Requirement**: Win any game for the first time
- **Icon**: 🏆
- **Is Active**: ✓ Yes

#### 2. Game Master Badge 👑
- **Name**: Game Master
- **Description**: Awarded for winning 10 games
- **Type**: BADGE
- **Value**: 0
- **Requirement**: Win the same game 10 times
- **Icon**: 👑
- **Is Active**: ✓ Yes

#### 3. Perfect Score Badge ⭐
- **Name**: Perfect Score
- **Description**: Awarded for maintaining 100% win rate
- **Type**: BADGE
- **Value**: 0
- **Requirement**: Maintain 100% win rate after playing 10+ games
- **Icon**: ⭐
- **Is Active**: ✓ Yes

#### 4. Quick Learner Badge 🎓
- **Name**: Quick Learner
- **Description**: Reach level 5
- **Type**: BADGE
- **Value**: 0
- **Requirement**: Accumulate 500 XP
- **Icon**: 🎓
- **Is Active**: ✓ Yes

#### 5. Scholar Badge 📚
- **Name**: Scholar
- **Description**: Reach level 10
- **Type**: BADGE
- **Value**: 0
- **Requirement**: Accumulate 1000 XP
- **Icon**: 📚
- **Is Active**: ✓ Yes

#### 6. Token Collector Badge 💰
- **Name**: Token Collector
- **Description**: Collect 1000 tokens
- **Type**: BADGE
- **Value**: 0
- **Requirement**: Earn 1000 total tokens
- **Icon**: 💰
- **Is Active**: ✓ Yes

### Optional Bonus Rewards

#### 7. XP Boost ✨
- **Name**: XP Boost
- **Description**: Bonus 50 XP
- **Type**: BONUS_XP
- **Value**: 50
- **Requirement**: Special achievement
- **Icon**: ✨
- **Is Active**: ✓ Yes

#### 8. Token Bonus 🎁
- **Name**: Token Bonus
- **Description**: Bonus 100 tokens
- **Type**: BONUS_TOKENS
- **Value**: 100
- **Requirement**: Special achievement
- **Icon**: 🎁
- **Is Active**: ✓ Yes

## Step 3: Create Games

Navigate to **Admin Panel → Games** or go to `/admin/game`

### Example Game Setup

**Easy Puzzle Game:**
- **Name**: Memory Match
- **Description**: Match pairs of cards
- **Type**: PUZZLE
- **Difficulty**: EASY
- **Token Cost**: 5 (students pay 5 tokens to play)
- **Reward Tokens**: 10 (students earn 10 tokens if they win)
- **Reward XP**: 25 (students earn 25 XP if they win)
- **Is Active**: ✓ Yes

**Medium Trivia Game:**
- **Name**: Math Quiz
- **Description**: Answer math questions
- **Type**: TRIVIA
- **Difficulty**: MEDIUM
- **Token Cost**: 10
- **Reward Tokens**: 25
- **Reward XP**: 50
- **Is Active**: ✓ Yes

**Hard Arcade Game:**
- **Name**: Speed Challenge
- **Description**: Complete tasks quickly
- **Type**: ARCADE
- **Difficulty**: HARD
- **Token Cost**: 20
- **Reward Tokens**: 50
- **Reward XP**: 100
- **Is Active**: ✓ Yes

## Step 4: Give Students Starting Tokens

### Option A: Via Database (Quick)
```sql
-- Give all students 100 starting tokens
UPDATE student_profile SET total_tokens = 100;

-- Give specific student tokens
UPDATE student_profile SET total_tokens = 100 WHERE id = 1;
```

### Option B: Via Admin Interface (Future Enhancement)
Create an admin interface to manage student tokens directly.

## Step 5: Test the System

1. **Login as a student**
2. **Navigate to Games** (`/games`)
3. **Select a game** to view details
4. **Click "Play Game"** (tokens will be deducted)
5. **Complete the game** (click "Complete Game" button)
6. **Check rewards**:
   - Flash message shows earned XP and tokens
   - View `/rewards/my-rewards` for badges
   - Check student dashboard for updated stats

## How Badges Are Automatically Awarded

The system automatically checks for milestones when a student wins a game:

### First Victory Badge
- **Trigger**: Student wins any game for the first time
- **Auto-awarded**: Yes
- **Requirement**: `timesWon == 1` on any game

### Game Master Badge
- **Trigger**: Student wins the same game 10 times
- **Auto-awarded**: Yes
- **Requirement**: `timesWon == 10` on a specific game

### Perfect Score Badge
- **Trigger**: Student maintains 100% win rate after 10+ games
- **Auto-awarded**: Yes
- **Requirement**: `timesPlayed >= 10` AND `winRate == 100%`

## Admin Panel Features

### Rewards Management (`/admin/rewards`)
- ✅ View all rewards
- ✅ Create new rewards
- ✅ Edit existing rewards
- ✅ Delete rewards
- ✅ Activate/deactivate rewards

### Games Management (`/admin/game`)
- ✅ View all games
- ✅ Create new games
- ✅ Edit existing games
- ✅ Delete games
- ✅ Set token costs and rewards

## Student Features

### Game Playing (`/games`)
- View all available games
- Filter by type (PUZZLE, MEMORY, TRIVIA, ARCADE)
- View game details and requirements
- Play games (requires sufficient tokens)
- Earn XP and tokens on completion

### Rewards (`/rewards/my-rewards`)
- View all earned badges
- See when badges were earned
- View which game awarded the badge
- Track total rewards collected

### Dashboard
- View current level
- View total XP
- View token balance
- View XP progress to next level
- View recent game activity
- View game statistics

## Troubleshooting

### Students can't play games
- **Check**: Do they have enough tokens?
- **Solution**: Give them starting tokens via database

### Badges not being awarded
- **Check**: Are the badges created with exact names?
  - "First Victory" (case-sensitive)
  - "Game Master" (case-sensitive)
  - "Perfect Score" (case-sensitive)
- **Check**: Are badges set to Active?
- **Solution**: Create badges with exact names as shown above

### Games not showing
- **Check**: Are games set to Active?
- **Solution**: Edit game and check "Is Active"

## Database Tables

### Main Tables
- `reward` - Stores all available rewards
- `game` - Stores all games
- `student_profile` - Stores student data (includes totalXP, totalTokens, level)
- `student_game_progress` - Tracks student performance per game
- `student_reward` - Tracks which rewards each student has earned

## Next Steps

1. Create the recommended badges above
2. Create some test games
3. Give students starting tokens
4. Test the complete flow
5. Monitor student progress
6. Add more badges and games as needed

## Support

If you need to reset a student's progress:
```sql
-- Reset specific student's game progress
DELETE FROM student_game_progress WHERE student_id = 1;

-- Reset specific student's rewards
DELETE FROM student_reward WHERE student_id = 1;

-- Reset specific student's XP and tokens
UPDATE student_profile SET total_xp = 0, total_tokens = 100, level = 1 WHERE id = 1;
```
