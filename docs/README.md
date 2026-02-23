# Nova Learning Platform - Study Session Enhancement

A comprehensive Symfony-based learning management system with advanced study session tracking, analytics, AI-powered recommendations, gamification, and productivity tools.

## 📚 Documentation

**All documentation is now organized in the `/docs/` folder.**

### Quick Links:
- **[Documentation Index](docs/INDEX.md)** - Complete guide to all documentation
- **[Quick Start: AI Games](docs/QUICK_START_AI_GAMES.md)** - Create trivia games in 3 minutes
- **[Latest Fixes](docs/FINAL_FIX_SUMMARY.md)** - Recent bug fixes and solutions
- **[Testing Guide](docs/TEST_TRIVIA_GAME.md)** - How to test the trivia game system

### Key Documentation:
- **AI & Games**: [AI Generator Usage](docs/AI_GENERATOR_USAGE.md), [Game System](docs/GAME_SYSTEM_SUMMARY.md)
- **Setup**: [OAuth Setup](docs/OAUTH_SETUP_GUIDE.md), [2FA Setup](docs/TWO_FACTOR_AUTHENTICATION.md)
- **Features**: [Rating System](docs/GAME_RATING_SYSTEM.md), [Leveling System](docs/LEVELING_AND_LEADERBOARD.md)

## Table of Contents

- [Features](#features)
- [Documentation](#documentation)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Routes and Features](#routes-and-features)
- [Background Jobs](#background-jobs)
- [API Integration](#api-integration)
- [Testing](#testing)
- [Deployment](#deployment)

## Features

### 🎮 Gamification System
- **AI-Powered Question Generator**: Generate trivia questions using Hugging Face AI
- **Multiple Game Types**: PUZZLE, MEMORY, TRIVIA, ARCADE
- **Game Categories**: Full games with rewards, Mini games for energy
- **Custom Content**: Create games with custom questions, words, and challenges
- **Rating System**: 5-star ratings with statistics
- **Token Economy**: Earn and spend tokens on games
- **XP & Leveling**: 60-level progression system with milestones
- **Leaderboards**: Compete with other students

### Core Study Session Management
- **Study Session Tracking**: Create, edit, and manage study sessions with detailed metadata
- **Session Completion**: Mark sessions as complete/incomplete with automatic streak updates
- **Mood & Energy Tracking**: Record emotional state and energy levels during study sessions
- **Pomodoro Timer**: Built-in 25-minute focus timer with break suggestions
- **Break Management**: Track break duration and count for better productivity insights

### Analytics & Visualization
- **Analytics Dashboard**: Comprehensive metrics including total study time, XP earned, and completion rates
- **Interactive Charts**: Chart.js visualizations for study time by course and XP over time
- **Time Range Filters**: View analytics for week, month, or year periods
- **Study Streak Tracking**: Monitor current and longest study streaks
- **Energy Pattern Analysis**: Identify optimal study times based on energy level patterns

### Calendar Integration
- **FullCalendar Interface**: Visual calendar view of all planned and completed sessions
- **Drag-and-Drop Rescheduling**: Easily move sessions to new dates/times
- **Quick Session Creation**: Click any date to create a new study session
- **Status Differentiation**: Color-coded display for planned vs completed sessions
- **Multiple Views**: Switch between month, week, and day views

### Content Management
- **Note-Taking System**: Create, edit, and search notes associated with study sessions
- **PDF Resource Management**: Upload and attach PDF study materials (max 10MB)
- **Tag-Based Organization**: Categorize sessions with custom tags for easy filtering
- **Resource Downloads**: Secure download of attached PDF resources

### External Integrations
- **YouTube Search**: Discover educational videos related to study topics
- **Wikipedia Integration**: Quick access to article summaries for research
- **AI-Powered Recommendations**: Personalized study suggestions using OpenAI/Gemini
- **OAuth Login**: Google and LinkedIn authentication
- **Note Summarization**: AI-generated summaries of study notes
- **Quiz Generation**: Automatically create quiz questions from study content

### Notifications & Communication
- **Session Reminders**: Email notifications 30 minutes before scheduled sessions
- **Weekly Progress Reports**: Automated weekly summaries of study activities
- **Achievement Notifications**: Celebrate milestone streaks (7, 30, 100 days)
- **Notification Preferences**: Opt-in/opt-out controls for email notifications

### Performance & Reliability
- **Caching Strategy**: 5-minute cache for analytics, 1-hour cache for API responses
- **Async Processing**: Background job handling for emails and long-running tasks
- **Circuit Breaker Pattern**: Automatic API failure handling with graceful degradation
- **Error Logging**: Comprehensive logging for debugging and monitoring

## Requirements

- PHP 8.1 or higher
- Composer 2.x
- MySQL 8.0 or MariaDB 10.11
- Node.js 16+ and npm (for frontend assets)
- Symfony 6.x
- wkhtmltopdf (for PDF generation)

### PHP Extensions
- pdo_mysql
- intl
- mbstring
- xml
- curl
- gd

## Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd nova-learning-platform
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install frontend dependencies**
```bash
npm install
```

4. **Configure environment variables**
```bash
cp .env.example .env
# Edit .env with your configuration (see Configuration section)
```

5. **Create database**
```bash
php bin/console doctrine:database:create
```

6. **Run migrations**
```bash
php bin/console doctrine:migrations:migrate
```

7. **Create upload directories**
```bash
mkdir -p public/uploads/study_sessions
mkdir -p public/uploads/rewards
mkdir -p public/uploads/books
```

8. **Build frontend assets**
```bash
npm run build
# Or for development with watch mode:
npm run dev
```

9. **Start the development server**
```bash
symfony server:start
# Or use PHP built-in server:
php -S localhost:8000 -t public
```

## Configuration

### Environment Variables

Edit your `.env` file with the following configurations:

#### Database Configuration
```env
DATABASE_URL="mysql://username:password@127.0.0.1:3306/nova_db?serverVersion=8.0.32&charset=utf8mb4"
```

#### Mailer Configuration (Gmail)
```env
# Get app password from: https://myaccount.google.com/apppasswords
MAILER_DSN=gmail://your_email@gmail.com:your_app_password@default
```

#### YouTube API (Required for video search)
```env
# Get your key from: https://console.cloud.google.com/apis/credentials
YOUTUBE_API_KEY=your_youtube_api_key_here
```

#### OpenWeatherMap API (Optional - for weather suggestions)
```env
# Get your key from: https://openweathermap.org/api
OPENWEATHER_API_KEY=your_openweather_api_key_here
```

#### OpenAI API (Required for AI features)
```env
# Get your key from: https://platform.openai.com/api-keys
OPENAI_API_KEY=your_openai_api_key_here
# Alternative: Use Gemini API (free tier available)
# GEMINI_API_KEY=your_gemini_api_key_here
```

**Note**: The system supports both OpenAI and Google Gemini APIs. If both are configured, OpenAI is used as primary with Gemini as fallback. See [AI_API_CONFIGURATION.md](AI_API_CONFIGURATION.md) for details.

#### File Upload Directory
```env
UPLOAD_DIRECTORY=uploads/study_sessions
```

#### Symfony Messenger (for async jobs)
```env
# Use Doctrine transport (default)
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
# Or use Redis for better performance:
# MESSENGER_TRANSPORT_DSN=redis://localhost:6379/messages
```

### API Key Setup Instructions

#### YouTube Data API v3
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable "YouTube Data API v3"
4. Go to Credentials → Create Credentials → API Key
5. Copy the API key to your `.env` file

#### OpenAI API
1. Go to [OpenAI Platform](https://platform.openai.com/)
2. Sign up or log in
3. Navigate to API Keys section
4. Create new secret key
5. Copy the key to your `.env` file

**Alternative: Google Gemini API (Free Tier Available)**
1. Go to [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Sign in with your Google account
3. Create API Key
4. Copy the key to your `.env` file as `GEMINI_API_KEY`

**Note**: The system will try OpenAI first, then fall back to Gemini if OpenAI fails. You can configure either or both. See [AI_API_CONFIGURATION.md](AI_API_CONFIGURATION.md) for detailed configuration guide.

#### OpenWeatherMap API (Optional)
1. Go to [OpenWeatherMap](https://openweathermap.org/api)
2. Sign up for a free account
3. Navigate to API Keys section
4. Copy your default API key to `.env` file

## Routes and Features

### Study Session Management

| Route | Method | Description | Access |
|-------|--------|-------------|--------|
| `/study-session` | GET | List all study sessions | ROLE_STUDENT |
| `/study-session/new` | GET, POST | Create new study session | ROLE_STUDENT |
| `/study-session/{id}` | GET | View session details | ROLE_STUDENT |
| `/study-session/{id}/edit` | GET, POST | Edit study session | ROLE_STUDENT |
| `/study-session/{id}/delete` | POST | Delete study session | ROLE_STUDENT |
| `/study-session/{id}/mark-complete` | POST | Mark session as completed | ROLE_STUDENT |
| `/study-session/{id}/mark-incomplete` | POST | Mark session as incomplete | ROLE_STUDENT |

### Analytics Dashboard

| Route | Method | Description | Access |
|-------|--------|-------------|--------|
| `/analytics` | GET | View analytics dashboard | ROLE_STUDENT AND ROLE_ADMIN
| `/analytics?range=week` | GET | Weekly analytics | ROLE_STUDENT AND ROLE_ADMIN
| `/analytics?range=month` | GET | Monthly analytics | ROLE_STUDENT AND ROLE_ADMIN
| `/analytics?range=year` | GET | Yearly analytics | ROLE_STUDENT AND ROLE_ADMIN

### Calendar Management

| Route | Method | Description | Access |
|-------|--------|-------------|--------|
| `/study-session/calendar` | GET | View calendar interface | ROLE_STUDENT |
| `/study-session/calendar/events` | GET | Get calendar events (JSON) | ROLE_STUDENT |
| `/study-session/calendar/update-datetime` | POST | Update session datetime | ROLE_STUDENT |
| `/study-session/calendar/create-from-date` | POST | Create session from date | ROLE_STUDENT |

### Notes Management

| Route | Method | Description | Access |
|-------|--------|-------------|--------|
| `/study-session/{id}/note/create` | GET, POST | Create note for session | ROLE_STUDENT |
| `/study-session/{id}/note/{noteId}/edit` | GET, POST | Edit note | ROLE_STUDENT |
| `/study-session/{id}/note/{noteId}/delete` | POST | Delete note | ROLE_STUDENT |
| `/study-session/{id}/note/search` | GET | Search notes by keyword | ROLE_STUDENT |

### Resource Management

| Route | Method | Description | Access |
|-------|--------|-------------|--------|
| `/study-session/{id}/resource/list` | GET | List session resources | ROLE_STUDENT AND ROLE_TUTOR |
| `/study-session/{id}/resource/upload` | GET, POST | Upload PDF resource | ROLE_TUTOR |
| `/study-session/{id}/resource/{resourceId}/download` | GET | Download PDF resource | ROLE_STUDENT |
| `/study-session/{id}/resource/{resourceId}/delete` | POST | Delete resource | ROLE_TUTOR |

### Tag Management

| Route | Method | Description | Access |
|-------|--------|-------------|--------|
| `/tag` | GET | List all tags with usage counts | ROLE_STUDENT |
| `/tag/new` | GET, POST | Create new tag | ROLE_STUDENT |
| `/tag/{id}/delete` | POST | Delete tag | ROLE_STUDENT |
| `/tag/{id}/filter` | GET | Filter sessions by tag | ROLE_STUDENT |

### Pomodoro Timer

| Route | Method | Description | Access |
|-------|--------|-------------|--------|
| `/study-session/{id}/pomodoro` | GET | View Pomodoro timer | ROLE_STUDENT |
| `/study-session/{id}/pomodoro/complete` | POST | Complete Pomodoro interval | ROLE_STUDENT |

### Energy Tracking

| Route | Method | Description | Access |
|-------|--------|-------------|--------|
| `/study-session/energy/analytics` | GET | View energy patterns | ROLE_STUDENT |
| `/study-session/energy/recommendations` | GET | Get optimal study times | ROLE_STUDENT |

### External Integrations

| Route | Method | Description | Access |
|-------|--------|-------------|--------|
| `/study-session/integration/youtube/search` | GET | Search YouTube videos | ROLE_STUDENT |
| `/study-session/integration/wikipedia/search` | GET | Search Wikipedia articles | ROLE_STUDENT |
| `/study-session/integration/weather/suggestions` | GET | Get weather-based suggestions | ROLE_STUDENT |
| `/study-session/integration/ai/recommendations` | GET | Get AI study recommendations | ROLE_STUDENT |
| `/study-session/integration/ai/summarize` | POST | Summarize notes with AI | ROLE_STUDENT |
| `/study-session/integration/ai/quiz` | POST | Generate quiz from content | ROLE_STUDENT |

### Course Management

| Route | Method | Description | Access |
|-------|--------|-------------|--------|
| `/courses` | GET | List all courses | All authenticated |
| `/courses/new` | GET, POST | Create new course | ROLE_TUTOR |
| `/courses/{id}` | GET | View course details | All authenticated |
| `/courses/{id}/edit` | GET, POST | Edit course | ROLE_TUTOR |
| `/courses/{id}/delete` | POST | Delete course | ROLE_TUTOR |

### Planning

| Route | Method | Description | Access |
|-------|--------|-------------|--------|
| `/planning` | GET | View study planning | ROLE_STUDENT |
| `/planning/new/{course}` | GET, POST | Create new planning | ROLE_STUDENT |
| `/planning/{id}` | GET | View planning details | ROLE_STUDENT |

## Background Jobs

The application uses Symfony Messenger for asynchronous job processing. The following background commands should be configured to run via cron:

### Cron Job Configuration

Add these entries to your crontab (`crontab -e`):

```bash
# Send session reminders every 5 minutes
*/5 * * * * cd /path/to/project && php bin/console app:study-session:send-reminders >> /var/log/cron.log 2>&1

# Check and reset streaks daily at midnight
0 0 * * * cd /path/to/project && php bin/console app:study-session:check-streaks >> /var/log/cron.log 2>&1

# Check and send achievement notifications daily at midnight
0 0 * * * cd /path/to/project && php bin/console app:study-session:check-achievements >> /var/log/cron.log 2>&1

# Send weekly progress reports every Sunday at 23:59
59 23 * * 0 cd /path/to/project && php bin/console app:study-session:send-weekly-reports >> /var/log/cron.log 2>&1

# Process async message queue (run continuously)
* * * * * cd /path/to/project && php bin/console messenger:consume async --time-limit=3600 >> /var/log/messenger.log 2>&1
```

### Background Commands

#### Send Session Reminders
```bash
php bin/console app:study-session:send-reminders
```
Sends email reminders 30 minutes before scheduled study sessions.

#### Check Streaks
```bash
php bin/console app:study-session:check-streaks
```
Checks for 24-hour gaps in study activity and resets streaks accordingly.

#### Check Achievements
```bash
php bin/console app:study-session:check-achievements
```
Identifies users who reached milestone streaks (7, 30, 100 days) and sends achievement notifications.

#### Send Weekly Reports
```bash
php bin/console app:study-session:send-weekly-reports
```
Generates and sends weekly progress reports to all active users.

#### Process Message Queue
```bash
php bin/console messenger:consume async
```
Processes queued background jobs (emails, AI requests, etc.). Should run continuously.

### Windows Task Scheduler (Alternative to Cron)

For Windows environments, use Task Scheduler:

1. Open Task Scheduler
2. Create Basic Task
3. Set trigger (e.g., "Daily at midnight")
4. Action: Start a program
5. Program: `C:\path\to\php.exe`
6. Arguments: `C:\path\to\project\bin\console app:study-session:check-streaks`

## API Integration

### YouTube Data API v3
- **Purpose**: Search educational videos related to study topics
- **Rate Limit**: 10,000 units per day (default quota)
- **Caching**: 1-hour cache for search results
- **Fallback**: Displays error message if API unavailable

### Wikipedia API
- **Purpose**: Quick access to article summaries for research
- **Rate Limit**: No official limit, but use responsibly
- **Caching**: 1-hour cache for search results
- **Fallback**: Displays error message if API unavailable

### OpenWeatherMap API (Optional)
- **Purpose**: Weather-based study time suggestions
- **Rate Limit**: 1,000 calls per day (free tier)
- **Caching**: 1-hour cache for weather data
- **Fallback**: Feature disabled if API unavailable

### OpenAI API
- **Purpose**: AI-powered recommendations, note summarization, quiz generation
- **Rate Limit**: Varies by plan (pay-as-you-go)
- **Caching**: Recommendations cached for 1 hour
- **Fallback**: Displays cached recommendations or generic tips

### Circuit Breaker Pattern
All external APIs implement a circuit breaker:
- After 3 consecutive failures, the integration is temporarily disabled
- Cached responses are served when available
- System continues functioning with degraded features

## Testing

### Run All Tests
```bash
php bin/phpunit
```

### Run Specific Test Suite
```bash
php bin/phpunit tests/Service/StudySession/
```

### Property-Based Tests
The application includes property-based tests using the Eris library:
```bash
php bin/phpunit --group "Feature: study-session-enhancement"
```

### Test Coverage
```bash
php bin/phpunit --coverage-html coverage/
```

## Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed deployment instructions including:
- Database migration steps
- Environment variable configuration
- File upload directory setup
- Symfony Messenger transport configuration
- Cron job configuration
- Production optimization tips

## Troubleshooting

### Common Issues

**Issue**: "Class not found" errors
```bash
# Clear cache and regenerate autoload
composer dump-autoload
php bin/console cache:clear
```

**Issue**: Database connection errors
```bash
# Verify DATABASE_URL in .env
# Test connection:
php bin/console doctrine:query:sql "SELECT 1"
```

**Issue**: File upload errors
```bash
# Check directory permissions
chmod -R 775 public/uploads
# Verify UPLOAD_DIRECTORY in .env
```

**Issue**: Messenger queue not processing
```bash
# Check messenger transport configuration
php bin/console messenger:stats
# Manually consume messages:
php bin/console messenger:consume async -vv
```

**Issue**: API integration failures
```bash
# Check API keys in .env
# View error logs:
tail -f var/log/dev.log
# Test API connectivity manually
```

## License

[Your License Here]

## Support

For issues and questions:
- Create an issue on GitHub
- Contact: [your-email@example.com]

## Credits

Built with:
- [Symfony](https://symfony.com/)
- [Doctrine ORM](https://www.doctrine-project.org/)
- [Chart.js](https://www.chartjs.org/)
- [FullCalendar](https://fullcalendar.io/)
- [Bootstrap](https://getbootstrap.com/)
