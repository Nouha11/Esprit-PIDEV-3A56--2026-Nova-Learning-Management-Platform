# Sample Rewards Setup Guide

## Overview
This guide helps you insert sample rewards into the database to enable better AI assistant responses.

## Installation

### Option 1: Using MySQL Command Line
```bash
mysql -u root -p nova_db < database_seeds/insert_sample_rewards.sql
```

### Option 2: Using phpMyAdmin
1. Open phpMyAdmin
2. Select the `nova_db` database
3. Click on the "SQL" tab
4. Copy and paste the contents of `insert_sample_rewards.sql`
5. Click "Go" to execute

### Option 3: Using Symfony Console
```bash
php bin/console dbal:run-sql "$(cat database_seeds/insert_sample_rewards.sql)"
```

## Rewards Included

### Level-Based Badges (6 rewards)
- Beginner Badge (Level 2)
- Bronze Explorer (Level 5)
- Silver Achiever (Level 10)
- Gold Champion (Level 15)
- Platinum Master (Level 20)
- Diamond Legend (Level 30)

### XP Milestones with Token Rewards (5 rewards)
- First Steps (100 XP → 50 tokens)
- Rising Star (500 XP → 100 tokens)
- Experience Hunter (1000 XP → 200 tokens)
- XP Master (2500 XP → 500 tokens)
- Experience Legend (5000 XP → 1000 tokens)

### Game Completion Achievements (5 rewards)
- First Victory (1 game)
- Game Enthusiast (10 games)
- Gaming Pro (25 games)
- Game Master (50 games)
- Ultimate Gamer (100 games)

### Game Type Specific (5 rewards)
- Trivia Novice (5 trivia games)
- Quiz Master (20 trivia games)
- Memory Champion (10 memory games)
- Puzzle Solver (15 puzzle games)
- Arcade Legend (10 arcade games)

### Streak Achievements (3 rewards)
- 3-Day Streak
- Week Warrior (7 days)
- Monthly Champion (30 days)

### Special Token Bonuses (3 rewards)
- Token Starter Pack (100 tokens)
- Token Booster (250 tokens)
- Token Jackpot (500 tokens)

### Difficulty-Based (4 rewards)
- Easy Mode Expert
- Medium Challenger
- Hard Mode Hero
- Difficulty Master

### Social Achievements (2 rewards)
- Favorite Collector (5 favorites)
- Game Curator (20 favorites)

### Performance Achievements (3 rewards)
- Perfect Score (100% on 1 game)
- Perfectionist (100% on 5 games)
- Speed Demon (under 30 seconds)

### XP Bonus Rewards (3 rewards)
- XP Boost Starter (50 XP)
- XP Boost Pro (100 XP)
- XP Boost Master (250 XP)

## Total: 39 Sample Rewards

## Reward Types

The reward table supports these types:
- **BADGE**: Achievement badges for milestones
- **ACHIEVEMENT**: Special accomplishments
- **BONUS_XP**: Extra XP rewards
- **BONUS_TOKENS**: Extra token rewards
- **LEVEL_MILESTONE**: Level-based rewards

## Verification

After inserting, verify the rewards were created:

```sql
SELECT COUNT(*) as total_rewards FROM reward;
SELECT name, type, requirement FROM reward ORDER BY type, name;
```

## Benefits for AI Assistant

With these rewards in place, the AI assistant can:
1. Provide specific, actionable recommendations
2. Suggest achievable next goals
3. Give clear progress milestones
4. Explain different reward types
5. Motivate students with concrete targets

## Example AI Responses

### Before (without rewards):
"Keep playing and you'll unlock rewards soon!"

### After (with rewards):
"You're at Level 12 with 920 XP! I recommend the Silver Achiever badge - you need just 80 more XP to reach Level 13. Play 2-3 more games and this badge is yours!"

## Customization

Feel free to modify the rewards:
- Change XP/level requirements
- Adjust token values
- Add new reward types
- Update descriptions
- Create themed reward sets

## Notes

- All rewards are set to `is_active = 1` (active)
- Timestamps use `NOW()` for current date/time
- Requirements are stored as text descriptions
- Token rewards have a `value` field for the token amount
- Badges and achievements have `value = 0`
