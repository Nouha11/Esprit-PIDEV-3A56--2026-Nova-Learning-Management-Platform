# Database Update Instructions for Game Reward System

## New Features Added
- Student game progress tracking
- Student reward system
- XP and token management for students
- Automatic badge awards for milestones

## New Entities Created
1. `StudentGameProgress` - Tracks student's performance in each game
2. `StudentReward` - Tracks rewards earned by students

## Updated Entities
1. `StudentProfile` - Added fields:
   - `totalXP` (int) - Total experience points
   - `totalTokens` (int) - Total tokens balance
   - `level` (int) - Student level based on XP

## Database Schema Update Commands

Run these commands in order:

```bash
# Option 1: Using Doctrine Schema Update (Quick for development)
php bin/console doctrine:schema:update --force

# Option 2: Using Migrations (Recommended for production)
php bin/console make:migration
php bin/console doctrine:migrations:migrate

# Verify the changes
php bin/console doctrine:schema:validate
```

## New Tables Created
- `student_game_progress` - Links students to games with progress stats
- `student_reward` - Links students to earned rewards

## Sample Data to Create

### Create Milestone Badges (Run in admin panel or via fixtures)

1. **First Victory Badge**
   - Name: First Victory
   - Type: BADGE
   - Description: Awarded for winning your first game
   - Value: 0
   - Icon: 🏆

2. **Game Master Badge**
   - Name: Game Master
   - Type: BADGE
   - Description: Awarded for winning 10 games
   - Value: 0
   - Icon: 👑

3. **Perfect Score Badge**
   - Name: Perfect Score
   - Type: BADGE
   - Description: Awarded for maintaining 100% win rate after 10+ games
   - Value: 0
   - Icon: ⭐

## Testing the System

1. Login as a student
2. Navigate to Games section
3. Play a game (tokens will be deducted if game has a cost)
4. Complete the game (click "Complete Game" button)
5. Check your rewards in "My Rewards" section
6. View your XP and level progress in your dashboard

## API Endpoints Updated

- `GET /games/{id}` - Now shows student progress if logged in
- `GET /games/{id}/play` - Now requires ROLE_STUDENT and checks token balance
- `POST /games/{id}/complete` - Now awards XP, tokens, and badges
- `GET /rewards/my-rewards` - Shows earned rewards for logged-in student
- `GET /rewards/browse` - Shows all available rewards with earned status
