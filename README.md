# NOVA – Learning Management Platform

## Overview

This project was developed as part of the **PIDEV – 3rd Year Engineering Program** at **Esprit School of Engineering** (Academic Year 2025–2026).

NOVA is a comprehensive full-stack learning management system that combines traditional course management with modern gamification, AI-powered features, and productivity tools. The platform enables students to track study sessions, earn rewards through games, collaborate on forums, and receive personalized AI recommendations.

## Features

### 🎮 Gamification & Rewards System
- **AI-Powered Question Generator**: Generate trivia questions using Hugging Face AI
- **Multiple Game Types**: PUZZLE, MEMORY, TRIVIA, ARCADE
- **Token Economy**: Earn and spend tokens on games
- **XP & Leveling**: 60-level progression system with milestone rewards
- **Achievement System**: Unlock badges and special rewards for completing challenges
- **Reward Types**: Badges, bonus tokens, bonus XP, and level milestone rewards
- **Leaderboards**: Real-time rankings and competition
- **Rating System**: 5-star game ratings with statistics
- **Favorite Games**: Personalized game collections
- **PDF Certificates**: Download achievement certificates with QR codes

### 📚 Study Session Management
- **Session Tracking**: Create, edit, and manage study sessions with detailed metadata
- **Pomodoro Timer**: Built-in productivity timer with customizable work/break intervals
  - 25-minute focus sessions with 5-minute short breaks
  - 15-minute long breaks after 4 completed sessions
  - Visual countdown timer with progress tracking
  - Audio notifications for session completion
  - Automatic session logging and statistics
- **Energy System**: Track and regenerate energy through mini-games
  - Energy depletes during study sessions
  - Restore energy by playing mini-games (breathing exercises, quick puzzles)
  - Energy level affects study effectiveness
- **Mood & Energy Tracking**: Record emotional state during study sessions
- **Study Streak Tracking**: Monitor current and longest study streaks
- **Break Management**: Track break duration and count for better productivity

### 📊 Analytics & Visualization
- **Analytics Dashboard**: Comprehensive metrics with Chart.js visualizations
- **Calendar Integration**: FullCalendar interface with drag-and-drop rescheduling
- **Time Range Filters**: View analytics for week, month, or year periods
- **Energy Pattern Analysis**: Identify optimal study times

### 🤖 AI Integration
- **AI Chat Assistant**: Context-aware study buddy powered by Hugging Face
- **Reward Recommendations**: Personalized suggestions based on progress
- **Note Summarization**: AI-generated summaries of study notes
- **Quiz Generation**: Automatically create quiz questions from content
- **Forum AI Assist**: NOVA AI helps answer student questions

### 📖 Course & Content Management
- **Course Creation**: Tutors can create and manage courses
- **Resource Management**: Upload and attach PDF study materials
- **Note-Taking System**: Create, edit, and search notes
- **Tag-Based Organization**: Categorize sessions with custom tags

### 📚 Library Management System
- **Book Inventory**: Comprehensive catalog of available books with details
- **Book Borrowing**: Students can borrow books with automated tracking
- **Loan Management**: Track borrowed books, due dates, and return status
- **Late Fee System**: Automatic calculation of late fees for overdue books
- **Payment Processing**: Handle book purchase and late fee payments
- **Search & Filter**: Find books by title, author, category, or availability
- **Book Reservations**: Reserve books that are currently borrowed
- **Inventory Analytics**: Track popular books and borrowing patterns

### 🎯 Quiz System with AI Assistance
- **Quiz Creation**: Tutors can create multiple-choice quizzes with images
- **AI-Powered Hints**: Students can request AI-generated hints for difficult questions
  - Context-aware hints based on question content
  - Progressive hint system (subtle → detailed)
  - Powered by Hugging Face AI
- **Quiz Reporting**: Students can report inappropriate or incorrect questions
- **Admin Moderation**: Review and manage reported quizzes
- **Quiz Statistics**: Track completion rates, average scores, and performance
- **Image Support**: Add visual elements to quiz questions
- **Pagination**: Browse quizzes efficiently with paginated views
- **Filtering & Sorting**: Filter quizzes by difficulty, category, or status

### 💬 Forum & Collaboration
- **Discussion Forums**: Create posts and engage in discussions
- **Comment System**: Reply to posts and participate in conversations
- **AI Summaries**: Get AI-generated summaries of discussions
- **Text Enhancement**: AI-powered grammar and formatting improvements

### 🔐 Authentication & Security
- **OAuth Integration**: Google and LinkedIn authentication
- **Two-Factor Authentication**: Enhanced security with 2FA
- **Role-Based Access**: Student, Tutor, and Admin roles
- **JWT Authentication**: Secure API access

### 📧 Notifications & Communication
- **Email Notifications**: Session reminders and achievement alerts
- **Weekly Progress Reports**: Automated summaries of study activities
- **Real-time Notifications**: In-app notification system

### 📄 PDF & QR Features
- **Certificate Generation**: PDF certificates for achievements
- **QR Code Integration**: QR codes for rewards and games
- **PDF Resources**: Upload and download study materials

## Tech Stack

### Frontend
- **HTML5/CSS3**: Modern responsive design
- **JavaScript (ES6+)**: Interactive features
- **Bootstrap 5**: UI framework with dark/light theme support
- **Chart.js**: Data visualization
- **FullCalendar**: Calendar interface
- **Stimulus**: JavaScript framework for Symfony
- **Turbo**: Fast page navigation

### Backend
- **PHP 8.2**: Server-side language
- **Symfony 6.4**: PHP framework
- **Doctrine ORM**: Database abstraction
- **Twig**: Template engine
- **Symfony Messenger**: Async job processing
- **Symfony Mailer**: Email handling

### Database
- **MySQL 8.0 / MariaDB 10.4**: Relational database

### APIs & Services
- **Hugging Face API**: AI text generation (Qwen 2.5-7B model)
- **Google Gemini API**: Alternative AI service
- **Groq API**: Fast AI inference
- **YouTube Data API v3**: Video search
- **Wikipedia API**: Article summaries
- **Google OAuth2**: Authentication
- **LinkedIn OAuth2**: Authentication

### Development Tools
- **Composer**: PHP dependency management
- **npm**: JavaScript package management
- **Symfony CLI**: Development server
- **PHPUnit**: Testing framework
- **Webpack Encore**: Asset management

### Third-Party Bundles
- **KnpPaginatorBundle**: Pagination
- **EndroidQrCodeBundle**: QR code generation
- **KnpSnappyBundle**: PDF generation (wkhtmltopdf)
- **VichUploaderBundle**: File uploads
- **LexikJWTAuthenticationBundle**: JWT authentication
- **SchebTwoFactorBundle**: Two-factor authentication
- **Symfony UX Chartjs**: Chart integration
- **Symfony UX Turbo**: Fast navigation

## Architecture

### Project Structure
```
Pi_web/
├── assets/              # Frontend assets (JS, CSS)
├── bin/                 # Console commands
├── config/              # Configuration files
├── database_seeds/      # Database seed files
├── migrations/          # Database migrations
├── public/              # Web root
│   ├── assets/         # Compiled assets
│   ├── uploads/        # User uploads
│   └── index.php       # Entry point
├── src/
│   ├── Command/        # Console commands
│   ├── Controller/     # Controllers
│   │   ├── Admin/     # Admin controllers
│   │   └── Front/     # Frontend controllers
│   ├── Entity/         # Doctrine entities
│   ├── Form/           # Form types
│   ├── Repository/     # Doctrine repositories
│   ├── Security/       # Security components
│   └── Service/        # Business logic services
├── templates/          # Twig templates
│   ├── admin/         # Admin templates
│   ├── front/         # Frontend templates
│   └── components/    # Reusable components
├── tests/              # PHPUnit tests
├── var/                # Cache and logs
└── vendor/             # Composer dependencies
```

### Key Design Patterns
- **MVC Architecture**: Symfony's Model-View-Controller pattern
- **Repository Pattern**: Data access abstraction
- **Service Layer**: Business logic separation
- **Dependency Injection**: Symfony's service container
- **Event-Driven**: Symfony event dispatcher
- **Circuit Breaker**: API failure handling

## Contributors

This project was developed by a team of engineering students at **Esprit School of Engineering**:

- **[Nouha Hamrouni](https://github.com/Nouha11)** - Full-Stack Developer
- **[Acil Jouini](https://github.com/aciljouini)** - Full-Stack Developer
- **[Said Hadj Abdallah](https://github.com/Ha-Said)** - Full-Stack Developer
- **[Oussema Ben Zinouba](https://github.com/obenzinouba)** - Full-Stack Developer
- **[Oumeyma Radhouani](https://github.com/oumeyma-radhouani)** - Full-Stack Developer
- **[Wassim Ouni](https://github.com/wisssouni)** - Full-Stack Developer

## Academic Context

**Institution**: Esprit School of Engineering – Tunisia  
**Program**: PIDEV – 3rd Year Engineering (3A)  
**Academic Year**: 2025–2026  
**Project Type**: Integrated Development Project (Projet Intégré de Développement)

This project demonstrates the practical application of software engineering principles, full-stack web development, and modern development practices learned throughout the engineering curriculum.

## Acknowledgments

We would like to thank:

- **Esprit School of Engineering** for providing the academic framework and resources
- Our **project supervisors** for their guidance and support

---

**Developed at Esprit School of Engineering – Tunisia**  
PIDEV 3A | Academic Year 2025–2026
