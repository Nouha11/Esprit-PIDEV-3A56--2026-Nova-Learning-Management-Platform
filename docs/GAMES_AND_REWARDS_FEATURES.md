# 🎮 Games & Rewards System - Feature Documentation

## Overview
This document describes the comprehensive gamification system implemented in the NOVA platform, including games, rewards, achievements, and AI-powered features.

---

## 📋 Table of Contents
1. [Feature 1: PDF Certificate System](#feature-1-pdf-certificate-system)
2. [Feature 2: QR Code + Pagination](#feature-2-qr-code--pagination)
3. [Feature 3: Google Charts Dashboard](#feature-3-google-charts-dashboard)
4. [Feature 4: Reward Email Notifications](#feature-4-reward-email-notifications)
5. [Feature 5: Activate/Deactivate Games](#feature-5-activatedeactivate-games)
6. [Feature 6: XP Level Algorithm + Leaderboard](#feature-6-xp-level-algorithm--leaderboard)
7. [Feature 7: Token Spending System](#feature-7-token-spending-system)
8. [Feature 8: AI Reward Recommendation](#feature-8-ai-reward-recommendation)
9. [Feature 9: Multicriteria Ajax Search & Filter](#feature-9-multicriteria-ajax-search--filter)
10. [Feature 10: Favorite Games List](#feature-10-favorite-games-list)
11. [Feature 11: Game Rating System](#feature-11-game-rating-system)
12. [Feature 12: Flash Messages System](#feature-12-flash-messages-system)
13. [Bonus Features](#bonus-features)
14. [Technology Stack](#technology-stack)

---

## Feature 1: PDF Certificate System
**Category:** Bundle (consistent) | **Complexity:** ⭐⭐⭐

### What it does
When a student earns an achievement-type reward, they can download a personalized PDF certificate with:
- Student's full name
- Reward/Achievement name
- Date earned
- Professional layout with decorative borders
- NOVA branding and logo

### Implementation Details

**Controller:** `src/Controller/Front/Game/RewardController.php`
- Method: `certificate(Reward $reward)`
- Route: `/rewards/{id}/certificate`

**Template:** `templates/front/game/certificate.html.twig`
- A4 landscape format (297mm x 210mm)
- Decorative corners and borders
- Professional typography
- Print-optimized CSS

**Bundle Used:** `knplabs/knp-snappy-bundle` (wkhtmltopdf wrapper)
- Converts HTML to PDF
- Configuration in `config/packages/knp_snappy.yaml`

**Access Control:**
- Only available for ACHIEVEMENT type rewards
- Student must have earned the reward
- Download button appears on reward detail page

---

## Feature 2: QR Code + Pagination
**Category:** Bundle (2x weak) | **Complexity:** ⭐⭐

### What it does
- Each reward has a unique QR code linking to its detail page
- Game and reward lists are paginated (6-12 items per page)
- Clean navigation without overwhelming users

### Implementation Details

**QR Code Generation:**
- **Bundle:** `endroid/qr-code-bundle`
- **Service:** `src/Service/game/RewardService.php`
- Method: `generateQRCode(Reward $reward)`
- QR codes stored in `public/uploads/qr_codes/`

**Pagination:**
- **Bundle:** `knplabs/knp-paginator-bundle`
- **Controller:** `src/Controller/Front/Game/GameController.php`
- Items per page: 12 for games, 6 for rewards
- Custom pagination template: `templates/pagination/custom_pagination.html.twig`
- Bootstrap 5 styled pagination controls

**Routes:**
- Games: `/games?page=1`
- Rewards: `/rewards?page=1`
- My Rewards: `/rewards/my-rewards?page=1`

---

## Feature 3: Google Charts Dashboard
**Category:** API | **Complexity:** ⭐⭐⭐

### What it does
Admin dashboard with interactive charts showing:
- Top users by XP (bar chart)
- Most played game types (pie chart)
- Reward distribution by type (doughnut chart)
- User role distribution
- Learning statistics

### Implementation Details

**Bundle:** `symfony/ux-chartjs`
- Chart.js integration for Symfony
- Server-side chart configuration
- Responsive and theme-aware

**Controllers:**
- `src/Controller/Admin/AdminDashboardController.php`
- `src/Controller/Admin/Quiz/QuizController.php` (statistics method)

**API Endpoints:**
- `/admin/api/learning-stats` - Returns JSON data for learning statistics
- Ajax-powered, no page refresh needed

**Chart Types:**
- Doughnut charts for distributions
- Bar charts for comparisons
- Responsive design with dark mode support

**Templates:**
- `templates/admin/dashboard/index.html.twig`
- `templates/admin/quiz/statistics.html.twig`

---

## Feature 4: Reward Email Notifications
**Category:** API (Mailer) | **Complexity:** ⭐⭐

### What it does
Automatic email notifications when students unlock rewards:
- Congratulatory message
- Reward details (name, description, icon)
- Current XP and token balance
- Styled HTML email template

### Implementation Details

**Service:** `src/Service/game/RewardNotificationService.php`
- Method: `sendRewardNotification(StudentProfile $student, Reward $reward)`
- Integrated with Symfony Mailer

**Mailer Configuration:**
- **Bundle:** `symfony/mailer` + `symfony/google-mailer`
- SMTP via Gmail API
- Configuration in `.env`: `MAILER_DSN=gmail+smtp://...`

**Email Template:** `templates/emails/reward_notification.html.twig`
- Responsive HTML design
- Reward icon display
- Student stats (XP, tokens, level)
- Call-to-action button

**Triggered When:**
- Student completes a game and earns a reward
- Level milestone reached
- Achievement unlocked

---

## Feature 5: Activate/Deactivate Games
**Category:** Métier | **Complexity:** ⭐⭐

### What it does
- Admin can toggle game status with one click
- Inactive games disappear from student view instantly
- Ajax-powered, no page reload
- Confirmation messages

### Implementation Details

**Controller:** `src/Controller/Admin/Game/GameAdminController.php`
- Method: `toggleActive(Game $game)`
- Route: `/admin/games/{id}/toggle-active`
- Returns JSON response

**Frontend:**
- JavaScript in `templates/admin/game/index.html.twig`
- Toggle switch UI with Bootstrap
- Real-time status update
- Flash message confirmation

**Database:**
- `Game` entity has `isActive` boolean field
- Doctrine ORM automatically filters inactive games in student queries

**Access Control:**
- Admin only (ROLE_ADMIN)
- Students cannot see inactive games in browse/play views

---

## Feature 6: XP Level Algorithm + Leaderboard
**Category:** Métier | **Complexity:** ⭐⭐⭐

### What it does
- 60-level progression system based on XP
- Dynamic level calculation
- Real-time leaderboard with rankings
- Live search and filtering

### Implementation Details

**Service:** `src/Service/game/LevelCalculatorService.php`
- Method: `calculateLevel(int $xp)`
- 60 levels with exponential XP requirements
- Level 1: 0-99 XP
- Level 2: 100-249 XP
- Level 60: 2,950,000+ XP

**Level Milestones:**
- Automatic rewards at levels 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60
- Service: `src/Service/game/LevelRewardService.php`
- Milestone popup modal with celebration animation
- Token bonuses awarded automatically

**Leaderboard:**
- **Controller:** `src/Controller/Front/Game/LeaderboardController.php`
- Route: `/leaderboard`
- Template: `templates/front/game/leaderboard.html.twig`
- Features:
  - Real-time search by username
  - Rank display with medals (🥇🥈🥉)
  - Profile pictures
  - XP and level display
  - Ajax-powered filtering

**XP Sources:**
- Game completion
- Quiz completion
- Course completion
- Study session completion
- Bonus rewards

---

## Feature 7: Token Spending System
**Category:** Métier | **Complexity:** ⭐⭐⭐

### What it does
- Games can cost tokens to play
- Pre-play balance check
- Insufficient funds alert with exact shortage
- Token deduction on game start
- Refund system if game not completed

### Implementation Details

**Controller:** `src/Controller/Front/Game/GameController.php`
- Method: `checkTokens(Game $game)` - Ajax endpoint
- Method: `play(Game $game)` - Deducts tokens
- Method: `complete(Game $game)` - Awards rewards

**Flow:**
1. Student clicks "Play Game"
2. Ajax call to `/games/{id}/check-tokens`
3. Server checks: `student.totalTokens >= game.tokenCost`
4. If insufficient: Show modal with missing amount
5. If sufficient: Show confirmation modal with balance preview
6. On confirm: Deduct tokens and start game
7. On completion: Award tokens + XP

**Modal Templates:**
- Confirmation: Shows current balance, cost, remaining balance
- Insufficient: Shows shortage amount, links to earn more tokens
- Ajax-powered, no page reload

**Session Management:**
- Stores pre-play balance in session
- Tracks game cost
- Calculates net gain/loss on completion

---

## Feature 8: AI Reward Recommendation
**Category:** IA (Hugging Face) | **Complexity:** ⭐⭐⭐⭐

### What it does
AI-powered widget that analyzes student progress and recommends next reward to pursue with motivational message.

### Implementation Details

**Service:** `src/Service/game/AIRewardRecommendationService.php`
- Uses Hugging Face Inference API
- Model: `meta-llama/Llama-3.3-70B-Instruct`
- Analyzes: XP, level, earned rewards, available rewards

**API Configuration:**
- Endpoint: `https://router.huggingface.co/novita/v3/openai/chat/completions`
- API Key stored in `.env`: `HUGGING_FACE_API_KEY`
- Temperature: 0.7 for creative responses
- Max tokens: 200

**Controller:** `src/Controller/Front/AI/AIAssistantController.php`
- Route: `/ai/assistant/recommendation`
- Returns JSON with AI-generated message

**Widget:** `templates/components/ai_chat_widget.html.twig`
- Floating chat interface
- Quick action buttons
- Chat history stored in localStorage (user-specific)
- Dark mode compatible

**AI Prompt Engineering:**
- Structured prompt with student context
- Reward catalog analysis
- Motivational tone
- Actionable recommendations

---

## Feature 9: Multicriteria Ajax Search & Filter
**Category:** Métier | **Complexity:** ⭐⭐⭐

### What it does
Real-time game filtering without page reload:
- Search by name
- Filter by type (TRIVIA, PUZZLE, ARCADE, etc.)
- Filter by difficulty (EASY, MEDIUM, HARD)
- Filter by price (FREE, PAID)
- Combine multiple filters simultaneously

### Implementation Details

**Controller:** `src/Controller/Front/Game/GameController.php`
- Method: `browse(Request $request)`
- Processes query parameters
- Returns filtered results

**Repository:** `src/Repository/Gamification/GameRepository.php`
- Method: `findByFilters(array $filters)`
- QueryBuilder with dynamic WHERE clauses
- Optimized SQL queries

**Frontend:**
- Template: `templates/front/game/browse.html.twig`
- JavaScript event listeners on filter inputs
- Debounced search (300ms delay)
- URL parameter updates
- Loading states

**Filter Options:**
- **Type:** TRIVIA, PUZZLE, ARCADE, SEQUENCE, MAPPING
- **Difficulty:** EASY, MEDIUM, HARD
- **Price:** FREE (0 tokens), PAID (>0 tokens)
- **Search:** Matches game name and description

---

## Feature 10: Favorite Games List
**Category:** Métier | **Complexity:** ⭐⭐

### What it does
- Students can mark games as favorites
- Heart icon toggle on game cards
- "My Favorites" page shows saved games
- One favorite per game per user

### Implementation Details

**Entity:** `src/Entity/Gamification/FavoriteGame.php`
- ManyToOne relationship with Student
- ManyToOne relationship with Game
- Unique constraint: (student_id, game_id)

**Controller:** `src/Controller/Front/Game/FavoriteGameController.php`
- Route: `/games/{id}/favorite/toggle` (POST)
- Ajax endpoint
- Returns JSON with new status

**Repository:** `src/Repository/Gamification/FavoriteGameRepository.php`
- Method: `findByStudent(StudentProfile $student)`
- Method: `isFavorite(StudentProfile $student, Game $game)`

**Frontend:**
- Heart icon with filled/outline states
- JavaScript toggle handler
- Instant UI feedback
- Template: `templates/front/game/favorites.html.twig`

**Database Table:** `favorite_games`
- Columns: id, student_id, game_id, created_at
- Indexes on student_id and game_id

---

## Feature 11: Game Rating System
**Category:** Métier | **Complexity:** ⭐⭐

### What it does
- Students rate games 1-5 stars after playing
- Average rating displayed on game cards
- One rating per game per user
- Rating updates are instant

### Implementation Details

**Entity:** `src/Entity/Gamification/GameRating.php`
- Fields: student, game, rating (1-5), created_at
- Unique constraint: (student_id, game_id)

**Controller:** `src/Controller/Front/Game/GameRatingController.php`
- Route: `/games/{id}/rate` (POST)
- Ajax endpoint
- Validates rating value (1-5)
- Updates or creates rating

**Repository:** `src/Repository/Gamification/GameRatingRepository.php`
- Method: `getAverageRating(Game $game)`
- Method: `getUserRating(StudentProfile $student, Game $game)`
- SQL AVG() function for calculations

**Frontend:**
- Star rating UI component
- Click to rate
- Visual feedback (filled/empty stars)
- Average rating badge on game cards
- Template: `templates/front/game/show.html.twig`

**Display:**
- Game cards show average rating
- Game detail page shows user's rating + average
- Rating count displayed

---

## Feature 12: Flash Messages System
**Category:** Bundle (weak) | **Complexity:** ⭐

### What it does
Styled notification messages for user actions:
- Success (green) - Game completion, rewards
- Error (red) - Critical errors
- Warning (yellow) - Insufficient tokens
- Info (blue) - Status updates

### Implementation Details

**Symfony Flash Messages:**
- Built-in Symfony feature
- Session-based storage
- Auto-dismiss after display

**Template:** `templates/base.html.twig`
- Bootstrap 5 alerts
- Icon integration (Bootstrap Icons)
- Auto-fade animation
- Dismissible

**Message Types:**
| Type | Color | Icon | Use Case |
|------|-------|------|----------|
| Success | Green | ✓ Check circle | Game completion, badges |
| Error | Red | ⚠ Warning triangle | Critical errors |
| Warning | Yellow | ⚠ Exclamation | Insufficient tokens |
| Info | Blue | ℹ Info circle | Status updates |

**Usage in Controllers:**
```php
$this->addFlash('success', 'Game completed! +50 XP');
$this->addFlash('warning', 'Insufficient tokens. Need 10 more.');
$this->addFlash('error', 'An error occurred.');
$this->addFlash('info', 'Game has been deactivated.');
```

---

## Bonus Features

### 1. Customized Reward Icons
- Custom icon upload for each reward
- Stored in `public/uploads/rewards/`
- **Bundle:** `vich/uploader-bundle`
- Automatic file handling and validation
- Fallback to default icons

### 2. Game Templates System
- Pre-built game templates for quick creation
- Template types: TRIVIA, PUZZLE, ARCADE, SEQUENCE, MAPPING
- JSON-based content structure
- Admin can create games from templates
- Template: `templates/admin/game/new.html.twig`

### 3. Mini Games for Energy Regeneration
- Special game category: MINI_GAME
- Restores energy points on completion
- Energy system integration
- Service: `src/Service/EnergyMonitorService.php`
- Pomodoro timer integration
- Energy bar widget: `templates/components/energy_bar_widget.html.twig`

### 4. Level Milestone Celebration Modal
- Animated popup when reaching milestone levels
- Confetti animation
- Displays milestone details
- Token bonus notification
- Template: `templates/front/game/show.html.twig`
- Multiple milestones shown sequentially

### 5. Study Buddy AI
- AI-powered study assistant
- Recommends courses from database
- Personalized study tips
- Widget: `templates/components/study_buddy_widget.html.twig`
- Controller: `src/Controller/Front/StudySession/StudyBuddyController.php`
- Uses Hugging Face API

### 6. Activity Tracking
- Logs all user activities
- Service: `src/Service/UserActivityService.php`
- Activity types: game_played, xp_earned, tokens_earned, level_up
- AI Activity Summary with recommendations
- Component: `templates/components/ai_activity_summary.html.twig`

---

## Technology Stack

### Backend Framework
- **Symfony 6.4** - PHP framework
- **PHP 8.1+** - Programming language
- **Doctrine ORM 3.6** - Database abstraction
- **MySQL/MariaDB** - Database

### Bundles & Libraries

#### Core Bundles
- `symfony/framework-bundle` - Core Symfony functionality
- `symfony/security-bundle` - Authentication & authorization
- `symfony/twig-bundle` - Template engine
- `doctrine/doctrine-bundle` - ORM integration
- `doctrine/doctrine-migrations-bundle` - Database migrations

#### Feature-Specific Bundles
- `knplabs/knp-snappy-bundle` - PDF generation (wkhtmltopdf)
- `endroid/qr-code-bundle` - QR code generation
- `knplabs/knp-paginator-bundle` - Pagination
- `symfony/ux-chartjs` - Chart.js integration
- `vich/uploader-bundle` - File uploads

#### Communication Bundles
- `symfony/mailer` - Email sending
- `symfony/google-mailer` - Gmail integration
- `symfony/notifier` - Notifications

#### Authentication & Security
- `scheb/2fa-bundle` - Two-factor authentication
- `lexik/jwt-authentication-bundle` - JWT tokens
- `knpuniversity/oauth2-client-bundle` - OAuth integration
- `league/oauth2-google` - Google OAuth
- `league/oauth2-linkedin` - LinkedIn OAuth

#### UI & Assets
- `symfony/asset-mapper` - Asset management
- `symfony/stimulus-bundle` - Stimulus.js integration
- `symfony/ux-turbo` - Turbo Drive for SPA-like experience

### Frontend Technologies
- **Bootstrap 5** - CSS framework
- **Bootstrap Icons** - Icon library
- **Chart.js** - Data visualization
- **JavaScript (Vanilla)** - Interactive features
- **Ajax/Fetch API** - Asynchronous requests
- **LocalStorage** - Client-side data persistence

### External APIs
- **Hugging Face Inference API** - AI recommendations
  - Model: `meta-llama/Llama-3.3-70B-Instruct`
  - Endpoint: `https://router.huggingface.co/novita/v3/openai/chat/completions`
- **Gmail SMTP** - Email delivery

### Database Schema

#### Core Tables
- `game` - Game definitions
- `reward` - Rewards and achievements
- `student_profile` - Student data (XP, tokens, level)
- `student_earned_rewards` - Many-to-many relationship

#### Feature Tables
- `favorite_games` - Student favorites
- `game_rating` - Game ratings
- `user_activities` - Activity log
- `level_milestone` - Level-based rewards

### File Structure

```
src/
├── Controller/
│   ├── Admin/
│   │   ├── Game/
│   │   │   ├── GameAdminController.php
│   │   │   ├── LevelMilestoneController.php
│   │   │   └── RewardAdminController.php
│   │   └── AdminDashboardController.php
│   └── Front/
│       ├── Game/
│       │   ├── GameController.php
│       │   ├── RewardController.php
│       │   ├── LeaderboardController.php
│       │   ├── FavoriteGameController.php
│       │   └── GameRatingController.php
│       └── AI/
│           └── AIAssistantController.php
├── Entity/
│   ├── Gamification/
│   │   ├── Game.php
│   │   ├── Reward.php
│   │   ├── FavoriteGame.php
│   │   └── GameRating.php
│   └── users/
│       └── StudentProfile.php
├── Service/
│   └── game/
│       ├── LevelCalculatorService.php
│       ├── LevelRewardService.php
│       ├── RewardService.php
│       ├── RewardNotificationService.php
│       ├── AIRewardRecommendationService.php
│       └── HuggingFaceService.php
└── Repository/
    └── Gamification/
        ├── GameRepository.php
        ├── RewardRepository.php
        ├── FavoriteGameRepository.php
        └── GameRatingRepository.php
```

```
templates/
├── front/
│   └── game/
│       ├── index.html.twig (Game list)
│       ├── browse.html.twig (Browse with filters)
│       ├── show.html.twig (Game detail)
│       ├── play.html.twig (Game play interface)
│       ├── certificate.html.twig (PDF certificate)
│       ├── reward_show.html.twig (Reward detail)
│       ├── my_rewards.html.twig (Student rewards)
│       ├── leaderboard.html.twig (XP rankings)
│       └── favorites.html.twig (Favorite games)
├── admin/
│   └── game/
│       ├── index.html.twig (Game management)
│       ├── new.html.twig (Create game)
│       ├── edit.html.twig (Edit game)
│       └── reward/
│           └── index.html.twig (Reward management)
└── components/
    ├── ai_chat_widget.html.twig
    ├── study_buddy_widget.html.twig
    ├── energy_bar_widget.html.twig
    └── ai_activity_summary.html.twig
```

---

## Configuration Files

### Environment Variables (.env)
```env
# Database
DATABASE_URL="mysql://user:pass@127.0.0.1:3306/nova_db"

# Mailer
MAILER_DSN=gmail+smtp://username:password@default

# AI API
HUGGING_FACE_API_KEY=your_api_key_here
```

### Doctrine Configuration
- `config/packages/doctrine.yaml` - ORM settings
- `config/packages/doctrine_migrations.yaml` - Migration settings

### Bundle Configuration
- `config/packages/knp_snappy.yaml` - PDF generation
- `config/packages/knp_paginator.yaml` - Pagination
- `config/packages/vich_uploader.yaml` - File uploads
- `config/packages/endroid_qr_code.yaml` - QR codes

---

## Key Design Patterns

### Service Layer Pattern
- Business logic separated from controllers
- Reusable services for common operations
- Dependency injection via constructor

### Repository Pattern
- Custom query methods in repositories
- QueryBuilder for complex queries
- Optimized database access

### Entity Relationships
- ManyToMany: Student ↔ Rewards (earned_rewards)
- ManyToOne: FavoriteGame → Student, Game
- ManyToOne: GameRating → Student, Game
- OneToMany: Game → Rewards

### Ajax Pattern
- Fetch API for asynchronous requests
- JSON responses from controllers
- Instant UI updates without page reload
- Loading states and error handling

---

## Performance Optimizations

1. **Database Indexing**
   - Indexes on foreign keys
   - Composite indexes for common queries
   - Unique constraints for data integrity

2. **Query Optimization**
   - Eager loading with JOIN
   - Pagination to limit results
   - Caching for frequently accessed data

3. **Asset Optimization**
   - CSS/JS minification
   - Image optimization
   - CDN for external libraries

4. **Frontend Performance**
   - Debounced search inputs
   - LocalStorage for client-side caching
   - Lazy loading for images
   - Minimal DOM manipulation

---

## Security Measures

1. **Authentication & Authorization**
   - Role-based access control (ROLE_STUDENT, ROLE_ADMIN)
   - IsGranted annotations on controllers
   - Session management

2. **Input Validation**
   - Symfony Form validation
   - Entity constraints
   - CSRF protection on forms

3. **SQL Injection Prevention**
   - Doctrine ORM parameterized queries
   - No raw SQL queries

4. **XSS Prevention**
   - Twig auto-escaping
   - HTML purification for user content

5. **API Security**
   - API keys stored in environment variables
   - Rate limiting on external API calls
   - Error handling without exposing sensitive data

---

## Testing & Quality Assurance

### Manual Testing Checklist
- ✅ Game creation and editing
- ✅ Reward unlocking and display
- ✅ PDF certificate generation
- ✅ QR code generation
- ✅ Token spending and balance checks
- ✅ XP calculation and level progression
- ✅ Leaderboard ranking
- ✅ Favorite games toggle
- ✅ Game rating system
- ✅ Email notifications
- ✅ AI recommendations
- ✅ Search and filter functionality
- ✅ Flash messages display
- ✅ Dark/light theme compatibility

---

## Future Enhancements

### Planned Features
1. **Multiplayer Games** - Real-time competitive games
2. **Team Challenges** - Group-based rewards
3. **Seasonal Events** - Limited-time games and rewards
4. **Achievement Badges** - Visual badge collection
5. **Reward Marketplace** - Spend tokens on virtual items
6. **Game Analytics** - Detailed play statistics
7. **Social Sharing** - Share achievements on social media
8. **Mobile App** - Native iOS/Android apps
9. **Gamification API** - External integration
10. **Advanced AI** - Personalized game recommendations

---

## Troubleshooting

### Common Issues

**PDF Generation Not Working**
- Check wkhtmltopdf installation
- Verify path in `knp_snappy.yaml`
- Ensure write permissions on temp directory

**QR Codes Not Displaying**
- Check `public/uploads/qr_codes/` directory exists
- Verify write permissions
- Clear cache: `php bin/console cache:clear`

**AI Recommendations Failing**
- Verify `HUGGING_FACE_API_KEY` in `.env`
- Check API quota/limits
- Review logs in `var/log/dev.log`

**Email Notifications Not Sending**
- Verify `MAILER_DSN` configuration
- Check Gmail app password
- Enable "Less secure app access" if needed

**Charts Not Loading**
- Run `composer require symfony/ux-chartjs`
- Clear cache
- Check browser console for JavaScript errors

---

## Maintenance Commands

### Database
```bash
# Create migration
php bin/console make:migration

# Run migrations
php bin/console doctrine:migrations:migrate

# Load fixtures (if available)
php bin/console doctrine:fixtures:load
```

### Cache
```bash
# Clear cache
php bin/console cache:clear

# Warm up cache
php bin/console cache:warmup
```

### Assets
```bash
# Install assets
php bin/console assets:install public

# Import map install
php bin/console importmap:install
```

---

## Credits & Attribution

### Third-Party Libraries
- **Symfony** - PHP Framework (MIT License)
- **Bootstrap** - CSS Framework (MIT License)
- **Chart.js** - Charting library (MIT License)
- **Bootstrap Icons** - Icon library (MIT License)
- **wkhtmltopdf** - PDF generation (LGPL License)
- **Hugging Face** - AI API provider

### Development Team
- Platform: NOVA - Intelligent Study Coaching Platform
- Version: 1.0
- Last Updated: 2024

---

## Conclusion

This comprehensive gamification system provides a complete solution for engaging students through games, rewards, and AI-powered features. The modular architecture allows for easy extension and maintenance, while the use of industry-standard bundles ensures reliability and security.

For questions or support, please refer to the main project documentation or contact the development team.


---

## Feature 13: AI Trivia Question Generator
**Category:** IA (Hugging Face) | **Complexity:** ⭐⭐⭐⭐

### What it does
AI-powered automatic generation of trivia questions for games:
- Generate 3-10 questions on any topic
- Three difficulty levels (EASY, MEDIUM, HARD)
- Multiple choice format (4 options)
- Automatic parsing and validation
- Instant integration into game creation

### Implementation Details

**Service:** `src/Service/game/HuggingFaceService.php`
- Method: `generateTriviaQuestions(string $topic, int $count, string $difficulty)`
- AI Model: `qwen/qwen2.5-7b-instruct` (Hugging Face)
- API Endpoint: `https://router.huggingface.co/novita/v3/openai/chat/completions`

**Controller:** `src/Controller/Admin/Game/AIQuestionGeneratorController.php`
- Route: `/admin/games/ai/generate-questions` (POST)
- Route: `/admin/games/ai/test-connection` (GET)
- JSON API endpoints for Ajax calls

**Question Format:**
```
Q1: [Question text]
A) [First choice]
B) [Second choice]
C) [Third choice]
D) [Fourth choice]
Correct: [A/B/C/D]
```

**Parsing Logic:**
- Regex pattern matching for question blocks
- Extracts question text, 4 choices, and correct answer
- Fallback to JSON format if available
- Validates all required components

**Difficulty Customization:**
- **EASY:** Simple language, beginner-friendly concepts
- **MEDIUM:** Moderately challenging, clear concepts
- **HARD:** Advanced concepts, detailed knowledge required

**AI Configuration:**
- Temperature: 0.7 (balanced creativity)
- Max tokens: 2000 (sufficient for 10 questions)
- Timeout: 30 seconds
- Error handling with detailed messages

**Frontend Integration:**
- Template: `templates/admin/game/new.html.twig`
- Template: `templates/admin/game/edit.html.twig`
- Ajax-powered generation
- Real-time question preview
- Edit before saving
- Loading states with spinner

**Usage Flow:**
1. Admin enters topic (e.g., "World History")
2. Selects question count (3-10)
3. Clicks "Generate with AI"
4. AI generates questions in ~5-10 seconds
5. Questions appear in editable form
6. Admin reviews/edits if needed
7. Saves game with generated questions

**Error Handling:**
- API key validation
- Connection testing
- Timeout handling
- Parsing failure recovery
- User-friendly error messages

---

## Feature 14: Game Template System
**Category:** Métier | **Complexity:** ⭐⭐⭐

### What it does
Pre-built game templates for instant game creation:
- 4 full game templates
- 4 mini game templates
- Pre-configured settings
- One-click game creation
- Difficulty presets

### Implementation Details

**Service:** `src/Service/game/GameTemplateService.php`
- Method: `getTemplates()` - Returns all templates
- Method: `getTemplate($category, $key)` - Get specific template
- Method: `getTemplateConfig($category, $key, $difficulty)` - Get config for creation

**Controller:** `src/Controller/Admin/Game/GameAdminController.php`
- Route: `/admin/games/templates` (GET) - Display templates
- Route: `/admin/games/templates/create` (POST) - Create from template
- Method: `templates()` - Show template gallery
- Method: `createFromTemplate()` - Instantiate game from template

**Template:** `templates/admin/game/templates.html.twig`
- Visual template gallery
- Category tabs (Full Games / Mini Games)
- Template cards with icons
- Difficulty selector modal
- Instant creation

### Full Game Templates

#### 1. Word Scramble
- **Type:** PUZZLE
- **Engine:** word_scramble
- **Description:** Unscramble words within time limit
- **Difficulty Settings:**
  - EASY: 5 words, 60s, 10 tokens, 20 XP
  - MEDIUM: 8 words, 45s, 20 tokens, 40 XP
  - HARD: 12 words, 30s, 30 tokens, 60 XP

#### 2. Memory Match
- **Type:** MEMORY
- **Engine:** memory_match
- **Description:** Match pairs of cards
- **Difficulty Settings:**
  - EASY: 6 pairs, 90s, 10 tokens, 20 XP
  - MEDIUM: 10 pairs, 120s, 20 tokens, 40 XP
  - HARD: 15 pairs, 150s, 30 tokens, 60 XP

#### 3. Quick Quiz
- **Type:** TRIVIA
- **Engine:** quick_quiz
- **Description:** Multiple choice questions
- **Difficulty Settings:**
  - EASY: 5 questions, 15s each, 10 tokens, 20 XP
  - MEDIUM: 8 questions, 12s each, 20 tokens, 40 XP
  - HARD: 10 questions, 10s each, 30 tokens, 60 XP

#### 4. Reaction Clicker
- **Type:** ARCADE
- **Engine:** reaction_clicker
- **Description:** Click targets before they disappear
- **Difficulty Settings:**
  - EASY: 10 targets, 2000ms, 10 tokens, 20 XP
  - MEDIUM: 15 targets, 1500ms, 20 tokens, 40 XP
  - HARD: 20 targets, 1000ms, 30 tokens, 60 XP

### Mini Game Templates (Energy Regeneration)

#### 1. Breathing Exercise
- **Type:** ARCADE
- **Engine:** breathing
- **Description:** Calm breathing for relaxation
- **Energy Restored:** 5 points
- **Duration:** ~2 minutes
- **Free to play**

#### 2. Quick Stretch
- **Type:** ARCADE
- **Engine:** stretch
- **Description:** Simple stretching exercises
- **Energy Restored:** 5 points
- **Duration:** ~3 minutes
- **Free to play**

#### 3. Eye Rest (20-20-20 Rule)
- **Type:** ARCADE
- **Engine:** eye_rest
- **Description:** Look away for 20 seconds
- **Energy Restored:** 3 points
- **Duration:** 20 seconds
- **Free to play**

#### 4. Hydration Break
- **Type:** ARCADE
- **Engine:** hydration
- **Description:** Take a water break
- **Energy Restored:** 3 points
- **Duration:** ~1 minute
- **Free to play**

### Template Configuration Structure

```php
[
    'name' => 'Template Name',
    'type' => 'TRIVIA|PUZZLE|ARCADE|MEMORY',
    'category' => 'FULL_GAME|MINI_GAME',
    'description' => 'Template description',
    'engine' => 'engine_identifier',
    'difficulty_settings' => [
        'EASY' => ['time' => 60, 'tokens' => 10, 'xp' => 20],
        'MEDIUM' => ['time' => 45, 'tokens' => 20, 'xp' => 40],
        'HARD' => ['time' => 30, 'tokens' => 30, 'xp' => 60],
    ],
    // OR for mini games
    'energy_points' => 5,
]
```

### Game Engines

Each template uses a specific game engine that defines gameplay mechanics:

**Full Game Engines:**
- `word_scramble` - Word unscrambling logic
- `memory_match` - Card matching mechanics
- `quick_quiz` - Quiz question display and validation
- `reaction_clicker` - Target clicking and timing

**Mini Game Engines:**
- `breathing` - Breathing exercise timer and animation
- `stretch` - Stretching routine guide
- `eye_rest` - 20-20-20 rule timer
- `hydration` - Water break reminder

### Template Creation Flow

1. Admin navigates to `/admin/games/templates`
2. Browses template gallery
3. Clicks "Use Template" on desired template
4. Modal appears with difficulty selection (for full games)
5. Optionally customizes name/description
6. Clicks "Create Game"
7. Game instantly created with all settings
8. Redirected to game list

### Benefits

1. **Speed:** Create games in seconds vs minutes
2. **Consistency:** Pre-tested configurations
3. **Balance:** Difficulty-appropriate rewards
4. **Variety:** Multiple game types available
5. **Customizable:** Can edit after creation

---

## Game Content Structure

### Game Entity Fields
```php
class Game {
    private string $name;              // Game title
    private string $description;       // Game description
    private string $type;              // TRIVIA, PUZZLE, ARCADE, etc.
    private string $category;          // FULL_GAME, MINI_GAME
    private string $difficulty;        // EASY, MEDIUM, HARD
    private int $tokenCost;            // Cost to play
    private int $rewardTokens;         // Tokens earned
    private int $rewardXP;             // XP earned
    private int $energyPoints;         // Energy restored (mini games)
    private bool $isActive;            // Visibility status
    private ?string $icon;             // Custom icon file
    private ?string $engine;           // Game engine identifier
    private ?array $content;           // JSON game content
}
```

### Content JSON Structure

**TRIVIA Games:**
```json
{
    "questions": [
        {
            "question": "What is the capital of France?",
            "choices": ["London", "Paris", "Berlin", "Madrid"],
            "correct": 1
        }
    ]
}
```

**PUZZLE Games:**
```json
{
    "words": ["EDUCATION", "LEARNING", "STUDY"],
    "timeLimit": 60
}
```

**ARCADE Games:**
```json
{
    "targets": 15,
    "speed": 1500,
    "duration": 60
}
```

**SEQUENCE Games:**
```json
{
    "sequences": [
        {
            "question": "Arrange these events in chronological order:",
            "items": ["Event A", "Event B", "Event C"],
            "correctOrder": [0, 1, 2]
        }
    ]
}
```

**MAPPING Games:**
```json
{
    "pairs": [
        {
            "question": "Match countries with capitals:",
            "left": ["France", "Germany", "Italy"],
            "right": ["Berlin", "Paris", "Rome"],
            "correctPairs": [[0,1], [1,0], [2,2]]
        }
    ]
}
```

---

## Code Organization

### Service Layer
```
src/Service/game/
├── HuggingFaceService.php          # AI API integration
├── AIRewardRecommendationService.php # AI reward suggestions
├── GameTemplateService.php         # Template management
├── GameService.php                 # Game business logic
├── RewardService.php               # Reward operations
├── RewardNotificationService.php   # Email notifications
├── LevelCalculatorService.php      # XP/Level calculations
└── LevelRewardService.php          # Milestone rewards
```

### Controller Layer
```
src/Controller/
├── Admin/Game/
│   ├── GameAdminController.php           # Game CRUD
│   ├── RewardAdminController.php         # Reward CRUD
│   ├── LevelMilestoneController.php      # Milestone management
│   └── AIQuestionGeneratorController.php # AI generation API
└── Front/Game/
    ├── GameController.php                # Student game play
    ├── RewardController.php              # Reward viewing/certificates
    ├── LeaderboardController.php         # XP rankings
    ├── FavoriteGameController.php        # Favorites management
    └── GameRatingController.php          # Rating system
```

### Repository Layer
```
src/Repository/Gamification/
├── GameRepository.php           # Game queries
├── RewardRepository.php         # Reward queries
├── FavoriteGameRepository.php   # Favorites queries
└── GameRatingRepository.php     # Rating queries
```

### Entity Layer
```
src/Entity/Gamification/
├── Game.php              # Game entity
├── Reward.php            # Reward entity
├── FavoriteGame.php      # Favorite relationship
└── GameRating.php        # Rating entity
```

### Template Layer
```
templates/
├── admin/game/
│   ├── index.html.twig       # Game list (admin)
│   ├── new.html.twig         # Create game (with AI)
│   ├── edit.html.twig        # Edit game (with AI)
│   ├── templates.html.twig   # Template gallery
│   └── reward/
│       └── index.html.twig   # Reward management
└── front/game/
    ├── index.html.twig       # Game list (student)
    ├── browse.html.twig      # Browse with filters
    ├── show.html.twig        # Game detail
    ├── play.html.twig        # Game play interface
    ├── certificate.html.twig # PDF certificate
    ├── reward_show.html.twig # Reward detail
    ├── my_rewards.html.twig  # Student rewards
    ├── leaderboard.html.twig # XP rankings
    └── favorites.html.twig   # Favorite games
```

---

## API Endpoints Summary

### Admin APIs
- `POST /admin/games/ai/generate-questions` - Generate trivia questions
- `GET /admin/games/ai/test-connection` - Test AI API
- `POST /admin/games/templates/create` - Create from template
- `POST /admin/games/{id}/toggle-active` - Toggle game status
- `GET /admin/api/learning-stats` - Dashboard statistics

### Student APIs
- `POST /games/{id}/check-tokens` - Check token balance
- `POST /games/{id}/complete` - Complete game
- `POST /games/{id}/favorite/toggle` - Toggle favorite
- `POST /games/{id}/rate` - Rate game
- `POST /ai/assistant/chat` - AI chat
- `POST /ai/assistant/recommendation` - AI reward recommendation

---

## Database Schema

### Core Tables

**game**
- id (PK)
- name
- description
- type (TRIVIA, PUZZLE, ARCADE, etc.)
- category (FULL_GAME, MINI_GAME)
- difficulty (EASY, MEDIUM, HARD)
- token_cost
- reward_tokens
- reward_xp
- energy_points
- is_active
- icon
- engine
- content (JSON)
- created_at
- updated_at

**reward**
- id (PK)
- name
- description
- type (ACHIEVEMENT, BONUS_TOKENS, BONUS_XP, LEVEL_MILESTONE)
- value
- required_level
- is_active
- icon
- qr_code
- created_at

**student_earned_rewards** (Many-to-Many)
- student_id (FK)
- reward_id (FK)
- earned_at

**favorite_games**
- id (PK)
- student_id (FK)
- game_id (FK)
- created_at
- UNIQUE(student_id, game_id)

**game_rating**
- id (PK)
- student_id (FK)
- game_id (FK)
- rating (1-5)
- created_at
- UNIQUE(student_id, game_id)

**user_activities**
- id (PK)
- user_id (FK)
- activity_type
- description
- metadata (JSON)
- created_at

---

## Environment Configuration

### Required Environment Variables

```env
# Database
DATABASE_URL="mysql://user:pass@127.0.0.1:3306/nova_db"

# Mailer (for reward notifications)
MAILER_DSN=gmail+smtp://username:password@default

# AI Services
HUGGING_FACE_API_KEY=your_huggingface_api_key_here

# PDF Generation (wkhtmltopdf path)
WKHTMLTOPDF_PATH=/usr/local/bin/wkhtmltopdf
```

### Service Configuration

**config/services.yaml**
```yaml
services:
    # AI Services
    App\Service\game\HuggingFaceService:
        arguments:
            $huggingFaceApiKey: '%env(HUGGING_FACE_API_KEY)%'
    
    App\Service\game\AIRewardRecommendationService:
        arguments:
            $huggingFaceApiKey: '%env(HUGGING_FACE_API_KEY)%'
```

---

## AI Integration Details

### Hugging Face API

**Base URL:** `https://router.huggingface.co/novita/v3/openai/chat/completions`

**Models Used:**
1. **Question Generation:** `qwen/qwen2.5-7b-instruct`
   - Specialized in text generation
   - Good at following structured formats
   - Fast response times

2. **Chat/Recommendations:** `meta-llama/Llama-3.3-70B-Instruct`
   - Better for conversational AI
   - More context-aware
   - Higher quality responses

**API Parameters:**
```json
{
    "model": "qwen/qwen2.5-7b-instruct",
    "messages": [
        {"role": "system", "content": "System prompt"},
        {"role": "user", "content": "User message"}
    ],
    "max_tokens": 2000,
    "temperature": 0.7,
    "timeout": 30
}
```

**Response Format:**
```json
{
    "choices": [
        {
            "message": {
                "content": "Generated text here"
            }
        }
    ]
}
```

### Error Handling

**API Errors:**
- 401: Invalid API key
- 429: Rate limit exceeded
- 500: Server error
- Timeout: Request took too long

**Fallback Strategies:**
- Retry with exponential backoff
- User-friendly error messages
- Logging for debugging
- Graceful degradation

---

## Advanced Features

### 1. Dynamic Difficulty Adjustment
- Games track player performance
- Difficulty can be adjusted based on success rate
- Reward scaling based on difficulty

### 2. Game Analytics
- Play count tracking
- Average completion time
- Success rate statistics
- Popular game identification

### 3. Reward Rarity System
- Common, Rare, Epic, Legendary tiers
- Visual indicators on reward cards
- Rarity affects token value

### 4. Achievement Chains
- Sequential achievements
- Unlock next achievement after completing previous
- Progressive difficulty increase

### 5. Daily Challenges
- Special games available for 24 hours
- Bonus rewards for completion
- Rotation system

---

## Best Practices

### Game Creation
1. Use templates for consistency
2. Test games before activating
3. Balance token cost vs rewards
4. Provide clear instructions
5. Set appropriate difficulty

### AI Question Generation
1. Be specific with topics
2. Review generated questions
3. Edit for clarity if needed
4. Test question difficulty
5. Ensure correct answers are accurate

### Reward Design
1. Make rewards meaningful
2. Balance token values
3. Use appealing icons
4. Write clear descriptions
5. Set appropriate level requirements

### Performance
1. Paginate large lists
2. Cache frequently accessed data
3. Optimize database queries
4. Minimize API calls
5. Use Ajax for better UX

---

## Summary

The Games & Rewards system is a comprehensive gamification platform featuring:

✅ **14 Major Features** - From PDF certificates to AI generation
✅ **4 Full Game Templates** - Instant game creation
✅ **4 Mini Game Templates** - Energy regeneration
✅ **AI-Powered** - Question generation and recommendations
✅ **60-Level System** - Progressive XP requirements
✅ **Email Notifications** - Automatic reward emails
✅ **QR Codes** - Unique codes for each reward
✅ **Charts & Analytics** - Visual statistics
✅ **Token Economy** - Spending and earning system
✅ **Leaderboard** - Competitive rankings
✅ **Favorites & Ratings** - User engagement
✅ **Flash Messages** - User feedback
✅ **Dark Mode** - Theme compatibility
✅ **Mobile Responsive** - Works on all devices

**Total Lines of Code:** ~15,000+
**Total Files:** ~50+
**Database Tables:** 10+
**API Endpoints:** 15+
**Bundles Used:** 15+

This system provides a complete, production-ready gamification solution for educational platforms.
