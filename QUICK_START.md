# Quick Start Guide - Game Reward System

## ✅ System is Ready!

The game reward system has been fully implemented. Here's how to get started:

## 1. Create Rewards (5 minutes)

1. Login as admin
2. Go to **Admin Panel → Rewards** (`/admin/rewards`)
3. Click **"Create New Reward"**
4. Create these 3 essential badges:

### Badge 1: First Victory 🏆
```
Name: First Victory
Description: Awarded for winning your first game
Type: BADGE
Value: 0
Requirement: Win any game for the first time
Icon: 🏆
Is Active: ✓
```

### Badge 2: Game Master 👑
```
Name: Game Master
Description: Awarded for winning 10 games
Type: BADGE
Value: 0
Requirement: Win the same game 10 times
Icon: 👑
Is Active: ✓
```

### Badge 3: Perfect Score ⭐
```
Name: Perfect Score
Description: Awarded for maintaining 100% win rate
Type: BADGE
Value: 0
Requirement: Maintain 100% win rate after playing 10+ games
Icon: ⭐
Is Active: ✓
```

## 2. Create a Test Game (2 minutes)

1. Go to **Admin Panel → Games** (`/admin/game`)
2. Click **"Create New Game"**
3. Fill in:
```
Name: Memory Match
Description: Match pairs of cards
Type: PUZZLE
Difficulty: EASY
Token Cost: 5
Reward Tokens: 10
Reward XP: 25
Is Active: ✓
```

## 3. Give Students Starting Tokens (1 minute)

Run this SQL in your database:
```sql
UPDATE student_profile SET total_tokens = 100;
```

## 4. Test It! (2 minutes)

1. **Login as a student**
2. **Go to** `/games`
3. **Click** on "Memory Match"
4. **Click** "Play Game" (5 tokens deducted)
5. **Click** "Complete Game"
6. **See the magic!** ✨
   - You earned 10 tokens + 25 XP
   - You got the "First Victory" badge 🏆
   - Your level increased!

## 5. Check Your Rewards

- Go to `/rewards/my-rewards` to see your badges
- Check your dashboard to see XP and level

## That's It!

The system is fully functional. Students can now:
- ✅ Play games and earn XP/tokens
- ✅ Automatically receive badges for milestones
- ✅ Level up based on XP
- ✅ Track their progress per game

## Admin Features Available

- `/admin/rewards` - Manage all rewards and badges
- `/admin/game` - Manage all games
- `/admin/dashboard` - View system statistics

## Student Features Available

- `/games` - Browse and play games
- `/rewards/my-rewards` - View earned badges
- `/rewards/browse` - See all available rewards
- Dashboard - View XP, level, tokens, and stats

## Need More Help?

See `MANUAL_SETUP_GUIDE.md` for detailed instructions.
