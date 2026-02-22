-- Sample Rewards for AI Assistant Testing
-- This creates a variety of rewards with clear requirements

-- Level-based Badges
INSERT INTO reward (name, description, type, requirement, value, is_active) VALUES
('Beginner Badge', 'Welcome to the platform! Awarded for reaching Level 2.', 'BADGE', 'Reach Level 2', 0, 1),
('Bronze Explorer', 'You''re making progress! Awarded for reaching Level 5.', 'BADGE', 'Reach Level 5', 0, 1),
('Silver Achiever', 'Halfway there! Awarded for reaching Level 10.', 'BADGE', 'Reach Level 10', 0, 1),
('Gold Champion', 'Outstanding performance! Awarded for reaching Level 15.', 'BADGE', 'Reach Level 15', 0, 1),
('Platinum Master', 'Elite status achieved! Awarded for reaching Level 20.', 'BADGE', 'Reach Level 20', 0, 1),
('Diamond Legend', 'Legendary achievement! Awarded for reaching Level 30.', 'BADGE', 'Reach Level 30', 0, 1);

-- XP Milestones with Token Rewards
INSERT INTO reward (name, description, type, requirement, value, is_active) VALUES
('First Steps', 'Earn your first 100 XP and get 50 bonus tokens!', 'BONUS_TOKENS', 'Earn 100 XP', 50, 1),
('Rising Star', 'Reach 500 XP and receive 100 bonus tokens!', 'BONUS_TOKENS', 'Earn 500 XP', 100, 1),
('Experience Hunter', 'Accumulate 1000 XP and claim 200 bonus tokens!', 'BONUS_TOKENS', 'Earn 1000 XP', 200, 1),
('XP Master', 'Reach 2500 XP and earn 500 bonus tokens!', 'BONUS_TOKENS', 'Earn 2500 XP', 500, 1),
('Experience Legend', 'Achieve 5000 XP and receive 1000 bonus tokens!', 'BONUS_TOKENS', 'Earn 5000 XP', 1000, 1);

-- Game Completion Achievements
INSERT INTO reward (name, description, type, requirement, value, is_active) VALUES
('First Victory', 'Complete your first game successfully!', 'ACHIEVEMENT', 'Complete 1 game', 0, 1),
('Game Enthusiast', 'Complete 10 games to show your dedication!', 'ACHIEVEMENT', 'Complete 10 games', 0, 1),
('Gaming Pro', 'Complete 25 games and prove your skills!', 'ACHIEVEMENT', 'Complete 25 games', 0, 1),
('Game Master', 'Complete 50 games to become a true master!', 'ACHIEVEMENT', 'Complete 50 games', 0, 1),
('Ultimate Gamer', 'Complete 100 games - you''re unstoppable!', 'ACHIEVEMENT', 'Complete 100 games', 0, 1);

-- Game Type Specific Achievements
INSERT INTO reward (name, description, type, requirement, value, is_active) VALUES
('Trivia Novice', 'Complete 5 trivia games and test your knowledge!', 'ACHIEVEMENT', 'Complete 5 TRIVIA games', 0, 1),
('Quiz Master', 'Complete 20 trivia games with excellence!', 'ACHIEVEMENT', 'Complete 20 TRIVIA games', 0, 1),
('Memory Champion', 'Complete 10 memory games and sharpen your mind!', 'ACHIEVEMENT', 'Complete 10 MEMORY games', 0, 1),
('Puzzle Solver', 'Complete 15 puzzle games and prove your logic!', 'ACHIEVEMENT', 'Complete 15 PUZZLE games', 0, 1),
('Arcade Legend', 'Complete 10 arcade games with speed and precision!', 'ACHIEVEMENT', 'Complete 10 ARCADE games', 0, 1);

-- Streak Achievements
INSERT INTO reward (name, description, type, requirement, value, is_active) VALUES
('3-Day Streak', 'Play games for 3 consecutive days!', 'ACHIEVEMENT', 'Play 3 days in a row', 0, 1),
('Week Warrior', 'Play games for 7 consecutive days!', 'ACHIEVEMENT', 'Play 7 days in a row', 0, 1),
('Monthly Champion', 'Play games for 30 consecutive days!', 'ACHIEVEMENT', 'Play 30 days in a row', 0, 1);

-- Special Token Bonuses
INSERT INTO reward (name, description, type, requirement, value, is_active) VALUES
('Token Starter Pack', 'Get 100 tokens to kickstart your journey!', 'BONUS_TOKENS', 'Complete tutorial', 100, 1),
('Token Booster', 'Earn 250 tokens for consistent gameplay!', 'BONUS_TOKENS', 'Play 5 games in one day', 250, 1),
('Token Jackpot', 'Win 500 tokens for exceptional performance!', 'BONUS_TOKENS', 'Score 100% on 3 games', 500, 1);

-- Difficulty-based Achievements
INSERT INTO reward (name, description, type, requirement, value, is_active) VALUES
('Easy Mode Expert', 'Complete 10 easy difficulty games!', 'ACHIEVEMENT', 'Complete 10 EASY games', 0, 1),
('Medium Challenger', 'Complete 10 medium difficulty games!', 'ACHIEVEMENT', 'Complete 10 MEDIUM games', 0, 1),
('Hard Mode Hero', 'Complete 10 hard difficulty games!', 'ACHIEVEMENT', 'Complete 10 HARD games', 0, 1),
('Difficulty Master', 'Complete games at all difficulty levels!', 'ACHIEVEMENT', 'Complete games at EASY, MEDIUM, and HARD', 0, 1);

-- Social Achievements
INSERT INTO reward (name, description, type, requirement, value, is_active) VALUES
('Favorite Collector', 'Add 5 games to your favorites!', 'ACHIEVEMENT', 'Favorite 5 games', 0, 1),
('Game Curator', 'Add 20 games to your favorites!', 'ACHIEVEMENT', 'Favorite 20 games', 0, 1);

-- Performance Achievements
INSERT INTO reward (name, description, type, requirement, value, is_active) VALUES
('Perfect Score', 'Achieve 100% score on any game!', 'ACHIEVEMENT', 'Score 100% on 1 game', 0, 1),
('Perfectionist', 'Achieve 100% score on 5 different games!', 'ACHIEVEMENT', 'Score 100% on 5 games', 0, 1),
('Speed Demon', 'Complete a game in under 30 seconds!', 'ACHIEVEMENT', 'Complete game in under 30 seconds', 0, 1);

-- XP Bonus Rewards
INSERT INTO reward (name, description, type, requirement, value, is_active) VALUES
('XP Boost Starter', 'Get 50 bonus XP to help you level up!', 'BONUS_XP', 'Complete first 3 games', 50, 1),
('XP Boost Pro', 'Earn 100 bonus XP for dedication!', 'BONUS_XP', 'Complete 15 games', 100, 1),
('XP Boost Master', 'Receive 250 bonus XP for excellence!', 'BONUS_XP', 'Score 100% on 10 games', 250, 1);
