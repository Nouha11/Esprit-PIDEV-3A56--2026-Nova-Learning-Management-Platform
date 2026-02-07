-- Insert default milestone badges for the game reward system
-- Run this SQL in your database after updating the schema

INSERT INTO reward (name, description, type, value, requirement, icon, is_active) VALUES
('First Victory', 'Awarded for winning your first game', 'BADGE', 0, 'Win any game for the first time', '🏆', 1),
('Game Master', 'Awarded for winning 10 games', 'BADGE', 0, 'Win the same game 10 times', '👑', 1),
('Perfect Score', 'Awarded for maintaining 100% win rate', 'BADGE', 0, 'Maintain 100% win rate after playing 10+ games', '⭐', 1),
('Quick Learner', 'Reach level 5', 'BADGE', 0, 'Accumulate 500 XP', '🎓', 1),
('Scholar', 'Reach level 10', 'BADGE', 0, 'Accumulate 1000 XP', '📚', 1),
('Token Collector', 'Collect 1000 tokens', 'BADGE', 0, 'Earn 1000 total tokens', '💰', 1),
('XP Boost', 'Bonus 50 XP', 'BONUS_XP', 50, 'Special achievement', '✨', 1),
('Token Bonus', 'Bonus 100 tokens', 'BONUS_TOKENS', 100, 'Special achievement', '🎁', 1);
