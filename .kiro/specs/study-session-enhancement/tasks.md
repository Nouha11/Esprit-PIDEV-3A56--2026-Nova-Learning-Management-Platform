# Implementation Plan: Study Session Enhancement

## Overview

This implementation plan breaks down the Study Session Enhancement feature into discrete, incremental coding tasks. The approach follows Symfony best practices and implements features in a logical order: data model extensions first, then core services, followed by controllers and UI components, and finally background jobs and integrations.

Each task builds on previous work, ensuring that code is integrated and functional at every step. Testing tasks are included as optional sub-tasks to validate correctness properties and edge cases.

## Tasks

- [ ] 0. Set up project dependencies and configuration
  - [x] 0.1 Install required Symfony bundles
    - Install symfony/mailer (if not already installed)
    - Install symfony/http-client (if not already installed)
    - Install symfony/cache (if not already installed)
    - _Requirements: Email notifications, API integrations, Caching_

  - [x] 0.2 Install frontend dependencies
    - Install Chart.js via npm/yarn for analytics visualization
    - Install FullCalendar via npm/yarn for calendar management
    - _Requirements: 2.4, 2.5, 4.1_

  - [x] 0.3 Configure API keys in .env file
    - Add YOUTUBE_API_KEY placeholder
    - Add OPENWEATHER_API_KEY placeholder
    - Add OPENAI_API_KEY (or GEMINI_API_KEY) placeholder
    - _Requirements: 6.1, 8.1, 9.2_

  - [x] 0.4 Set up file upload directory
    - Create uploads directory for PDF resources
    - Configure directory path in services.yaml
    - _Requirements: 14.2_

- [x] 1. Extend data models and create new entities
  - [x] 1.1 Add new fields to StudySession entity
    - Add mood (string, nullable, values: 'positive', 'neutral', 'negative')
    - Add energyLevel (string, nullable, values: 'low', 'medium', 'high')
    - Add breakDuration (integer, nullable)
    - Add breakCount (integer, nullable)
    - Add pomodoroCount (integer, nullable)
    - Add completedAt (DateTime, nullable)
    - Update StudySession entity in `src/Entity/StudySession/StudySession.php`
    - _Requirements: 1.1, 1.2, 1.3, 18.2_

  - [x] 1.2 Create Tag entity
    - Create `src/Entity/StudySession/Tag.php`
    - Add id, name (unique, max 50 chars), createdAt fields
    - Add ManyToMany relationship with StudySession
    - _Requirements: 1.4, 16.1, 16.3_

  - [x] 1.3 Create Note entity
    - Create `src/Entity/StudySession/Note.php`
    - Add id, studySession (ManyToOne), content (max 10,000 chars), createdAt, updatedAt fields
    - _Requirements: 1.5, 15.1_

  - [x] 1.4 Create Resource entity
    - Create `src/Entity/StudySession/Resource.php`
    - Add id, studySession (ManyToOne), filename, storedFilename, fileSize, mimeType, uploadedAt fields
    - _Requirements: 1.6, 14.2_

  - [x] 1.5 Create StudyStreak entity
    - Create `src/Entity/StudySession/StudyStreak.php`
    - Add id, user (OneToOne), currentStreak, longestStreak, lastStudyDate, updatedAt fields
    - _Requirements: 3.1, 3.4_

  

  - [ ]* 1.7 Write property test for tag uniqueness
    - **Property 2: Tag uniqueness enforcement**
    - **Validates: Requirements 16.3**

- [x] 2. Create database migration
  - [x] 2.1 Generate and customize migration for all entity changes
    - Run `php bin/console make:migration`
    - Review and adjust the generated migration file
    - Add indexes for performance (tag.name, note.study_session_id, resource.study_session_id)
    - Run `php bin/console doctrine:migrations:migrate` to apply
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6_

- [x] 3. Create repositories for new entities (if they don't exist)
  - [x] 3.1 Create TagRepository(if they don't exist)
    - Create `src/Repository/StudySession/TagRepository.php`
    - Add method to find tag by name (case-insensitive)
    - Add method to get tags with usage counts
    - _Requirements: 16.2, 16.5_

  - [x] 3.2 Create NoteRepository (if they don't exist)
    - Create `src/Repository/StudySession/NoteRepository.php`
    - Add method to find notes by study session (ordered by createdAt DESC)
    - Add method to search notes by keyword
    - _Requirements: 15.4, 15.5_

  - [x] 3.3 Create ResourceRepository (if they don't exist)
    - Create `src/Repository/StudySession/ResourceRepository.php`
    - Add method to find resources by study session
    - _Requirements: 14.4_

  - [x] 3.4 Create StudyStreakRepository (if they don't exist)
    - Create `src/Repository/StudySession/StudyStreakRepository.php`
    - Add method to find or create streak for user
    - _Requirements: 3.1, 3.2_

  - [ ]* 3.5 Write property test for note ordering
    - **Property 3: Note ordering consistency**
    - **Validates: Requirements 15.4**

- [x] 4. Implement core service layer - Analytics
  - [x] 4.1 Create AnalyticsService
    - Create `src/Service/StudySession/AnalyticsService.php`
    - Implement getTotalStudyTime method (sum durations for completed sessions in date range)
    - Implement getTotalXP method (sum XP for completed sessions in date range)
    - Implement getCompletionRate method (completed / total * 100)
    - Implement getStudyTimeByCourse method (group by course, sum durations)
    - Implement getXPOverTime method (group by date, sum XP)
    - Implement getEnergyPatterns method (analyze energy levels by time of day)
    - _Requirements: 2.1, 2.2, 2.7, 2.8, 13.1, 19.1, 19.2, 19.3_

  - [ ]* 4.2 Write property tests for analytics calculations
    - **Property 5: Metric aggregation correctness**
    - **Validates: Requirements 2.1, 2.2, 19.1, 19.2**

  - [ ]* 4.3 Write property test for completion rate
    - **Property 6: Completion rate calculation**
    - **Validates: Requirements 2.7, 18.3, 19.3**

  - [ ]* 4.4 Write property test for time range filtering
    - **Property 7: Time range filtering**
    - **Validates: Requirements 2.6, 19.4**

  - [ ]* 4.5 Write property test for empty data handling
    - **Property 9: Empty data handling**
    - **Validates: Requirements 19.5**

- [x] 5. Implement core service layer - Streak tracking
  - [x] 5.1 Create StreakService
    - Create `src/Service/StudySession/StreakService.php`
    - Implement updateStreak method (increment if new day, update lastStudyDate)
    - Implement getCurrentStreak method
    - Implement getLongestStreak method
    - Implement checkAndResetStreak method (reset if >24 hours since last study)
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

  - [ ]* 5.2 Write property test for streak increment
    - **Property 10: Streak increment on completion**
    - **Validates: Requirements 3.1, 3.5**

  - [ ]* 5.3 Write property test for streak reset
    - **Property 11: Streak reset on gap**
    - **Validates: Requirements 3.2**

  - [ ]* 5.4 Write property test for completed sessions only
    - **Property 12: Completed sessions only count**
    - **Validates: Requirements 3.3**

  - [ ]* 5.5 Write property test for longest streak tracking
    - **Property 13: Longest streak tracking**
    - **Validates: Requirements 3.4**

- [x] 6. Implement resource management service
  - [x] 6.1 Create ResourceManager service
    - Create `src/Service/StudySession/ResourceManager.php`
    - Implement uploadPDF method (validate, generate unique filename, store file)
    - Implement deletePDF method (remove file from storage)
    - Implement validatePDF method (check MIME type, size <= 10MB)
    - Implement getStoragePath method
    - Configure upload directory in services.yaml
    - _Requirements: 14.1, 14.2, 14.3, 14.5_

  - [ ]* 6.2 Write property test for PDF format validation
    - **Property 38: PDF format validation**
    - **Validates: Requirements 14.1**

  - [ ]* 6.3 Write property test for unique filename generation
    - **Property 39: PDF unique filename generation**
    - **Validates: Requirements 14.2**

  - [ ]* 6.4 Write property test for resource cascade deletion
    - **Property 4: Resource cascade deletion**
    - **Validates: Requirements 14.5**

  - [ ]* 6.5 Write unit tests for file upload edge cases
    - Test file size exceeding 10MB
    - Test invalid MIME types
    - Test storage failures
    - _Requirements: 14.3_

- [x] 7. Implement external API clients - YouTube
  - [x] 7.1 Create YouTubeApiClient service
    - Create `src/Service/StudySession/YouTubeApiClient.php`
    - Implement searchVideos method (query YouTube Data API v3)
    - Parse response into YouTubeVideo objects
    - Limit results to 10 videos
    - Add 10-second timeout
    - Handle API errors gracefully
    - Store API key in .env file
    - _Requirements: 6.1, 6.2, 6.3, 6.5, 20.1_

  - [ ]* 7.2 Write property test for API result limiting
    - **Property 21: API result limiting**
    - **Validates: Requirements 6.5**

  - [ ]* 7.3 Write unit tests for YouTube API error handling
    - Test API unavailable scenario
    - Test timeout scenario
    - Test rate limit scenario
    - _Requirements: 6.4, 20.1, 20.2, 20.4_

- [x] 8. Implement external API clients - Wikipedia
  - [x] 8.1 Create WikipediaApiClient service
    - Create `src/Service/StudySession/WikipediaApiClient.php`
    - Implement searchArticles method (query Wikipedia API)
    - Implement getArticleSummary method
    - Parse response into WikipediaArticle objects
    - Add 10-second timeout
    - Handle API errors gracefully
    - _Requirements: 7.1, 7.2, 7.3, 20.1_

  - [ ]* 8.2 Write unit tests for Wikipedia API error handling
    - Test API unavailable scenario
    - Test no results found scenario
    - Test timeout scenario
    - _Requirements: 7.4, 7.5, 20.1_

- [ ] 9. Implement external API clients - Weather
  - [ ] 9.1 Create WeatherApiClient service
    - Create `src/Service/StudySession/WeatherApiClient.php`
    - Implement getCurrentWeather method (query OpenWeatherMap API)
    - Implement getForecast method
    - Implement getStudySuggestions method (analyze weather data)
    - Parse response into WeatherData objects
    - Add 10-second timeout
    - Handle API errors gracefully
    - Store API key in .env file
    - _Requirements: 8.1, 8.2, 8.3, 8.5, 20.1_

  - [ ]* 9.2 Write unit tests for Weather API error handling
    - Test API unavailable scenario
    - Test timeout scenario
    - _Requirements: 8.4, 20.1_

- [x] 10. Implement API error handling infrastructure
  - [x] 10.1 Create ApiErrorHandler service
    - Create `src/Service/StudySession/ApiErrorHandler.php`
    - Implement timeout handling (10 seconds)
    - Implement error response parsing
    - Implement circuit breaker pattern (disable after 3 consecutive failures)
    - Implement logging for all API errors
    - _Requirements: 20.1, 20.2, 20.5_

  - [ ]* 10.2 Write property tests for API error handling
    - **Property 23: Graceful API degradation**
    - **Validates: Requirements 6.4, 7.4, 8.4, 9.6, 10.4, 11.5, 20.3**

  - [ ]* 10.3 Write property test for API timeout
    - **Property 24: API timeout handling**
    - **Validates: Requirements 20.1**

  - [ ]* 10.4 Write property test for circuit breaker
    - **Property 27: Circuit breaker activation**
    - **Validates: Requirements 20.5**

- [x] 11. Implement AI-powered services
  - [x] 11.1 Create AIRecommendationService
    - Create `src/Service/StudySession/AIRecommendationService.php`
    - Implement generateStudyRecommendations method (send session data to OpenAI/Gemini API)
    - Implement summarizeNotes method (send notes to AI API)
    - Implement generateQuiz method (send content to AI API, generate 5-10 questions)
    - Parse AI responses into structured objects
    - Add 10-second timeout
    - Handle API errors gracefully (return cached recommendations or generic tips)
    - Store API key in .env file
    - _Requirements: 9.1, 9.2, 9.3, 10.1, 10.2, 10.3, 11.1, 11.2, 11.3_

  - [ ]* 11.2 Write property test for low energy recommendations
    - **Property 29: Low energy recommendations**
    - **Validates: Requirements 9.4**

  - [ ]* 11.3 Write property test for high completion recommendations
    - **Property 30: High completion recommendations**
    - **Validates: Requirements 9.5**

  - [ ]* 11.4 Write property test for quiz question count
    - **Property 31: Quiz question count**
    - **Validates: Requirements 11.3**

  - [ ]* 11.5 Write property test for quiz answer validation
    - **Property 32: Quiz answer validation**
    - **Validates: Requirements 11.4**

  - [ ]* 11.6 Write unit tests for AI API error handling
    - Test API unavailable scenario
    - Test empty notes scenario
    - Test timeout scenario
    - _Requirements: 9.6, 10.4, 11.5_

- [x] 12. Implement caching service
  - [x] 12.1 Create CacheService for analytics and API responses
    - Create `src/Service/StudySession/CacheService.php`
    - Implement cache wrapper for analytics data (5-minute TTL)
    - Implement cache wrapper for API responses (1-hour TTL)
    - Implement cache invalidation on session create/update
    - Use Symfony Cache component
    - _Requirements: 22.1, 22.2, 22.3, 22.4, 22.5_

  - [ ]* 12.2 Write property tests for caching behavior
    - **Property 56: Analytics cache duration**
    - **Property 57: API response cache duration**
    - **Property 58: Cache invalidation on update**
    - **Property 59: Cache expiration and refresh**
    - **Validates: Requirements 22.1, 22.2, 22.3, 22.4, 22.5**

- [x] 13. Implement notification service
  - [x] 13.1 Create NotificationService
    - Create `src/Service/StudySession/NotificationService.php`
    - Implement sendSessionReminder method (queue email 30 minutes before session)
    - Implement sendWeeklyProgressReport method (include total time, XP, sessions, streak)
    - Implement sendAchievementNotification method (for 7, 30, 100-day streaks)
    - Check user notification preferences before sending
    - Use Symfony Mailer and Messenger for async email sending
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6_

  - [ ]* 13.2 Write property test for reminder scheduling
    - **Property 17: Reminder scheduling**
    - **Validates: Requirements 5.1**

  - [ ]* 13.3 Write property test for reminder content
    - **Property 18: Reminder content completeness**
    - **Validates: Requirements 5.2**

  - [ ]* 13.4 Write property test for weekly report content
    - **Property 19: Weekly report content**
    - **Validates: Requirements 5.4**

  - [ ]* 13.5 Write property test for notification opt-out
    - **Property 20: Notification opt-out respect**
    - **Validates: Requirements 5.6**

  - [ ]* 13.6 Write unit tests for milestone achievements
    - Test 7-day streak notification
    - Test 30-day streak notification
    - Test 100-day streak notification
    - _Requirements: 5.5_

- [x] 14. Implement Pomodoro service
  - [x] 14.1 Create PomodoroService
    - Create `src/Service/StudySession/PomodoroService.php`
    - Implement startPomodoro method (return timer state with 25-minute countdown)
    - Implement completePomodoro method (increment pomodoro count)
    - Implement getBreakDuration method (5 minutes for <4 pomodoros, 15 minutes for 4+)
    - _Requirements: 12.1, 12.2, 12.3, 12.6_

  - [ ]* 14.2 Write property test for timer pause-resume
    - **Property 33: Timer pause-resume round-trip**
    - **Validates: Requirements 12.4, 12.5**

  - [ ]* 14.3 Write property test for pomodoro count persistence
    - **Property 34: Pomodoro count persistence**
    - **Validates: Requirements 12.6**

- [ ] 15. Checkpoint - Ensure all service tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 16. Create form types
  - [x] 16.1 Update StudySessionType form
    - Update `src/Form/StudySession/StudySessionType.php`
    - Add mood field (ChoiceType with positive/neutral/negative)
    - Add energyLevel field (ChoiceType with low/medium/high)
    - Add breakDuration field (IntegerType)
    - Add breakCount field (IntegerType)
    - Add tags field (EntityType, multiple, Tag class)
    - _Requirements: 1.1, 1.2, 1.3, 1.4_

  - [x] 16.2 Create NoteType form
    - Create `src/Form/StudySession/NoteType.php`
    - Add content field (TextareaType, maxlength 10,000)
    - _Requirements: 15.1, 15.6_

  - [x] 16.3 Create ResourceUploadType form
    - Create `src/Form/StudySession/ResourceUploadType.php`
    - Add file field (FileType with PDF validation, max 10MB)
    - _Requirements: 14.1, 14.3_

  - [x] 16.4 Create TagType form
    - Create `src/Form/StudySession/TagType.php`
    - Add name field (TextType, max 50 chars, unique validation)
    - _Requirements: 16.3, 16.6_

  - [ ]* 16.5 Write property tests for form validation
    - **Property 51: Required field validation**
    - **Property 52: File upload validation**
    - **Property 54: Datetime validation**
    - **Property 55: Validation error specificity**
    - **Validates: Requirements 21.1, 21.2, 21.4, 21.5**

- [x] 17. Create controllers - Study session management
  - [x] 17.1 Create or update StudySessionController
    - Create/update `src/Controller/StudySession/StudySessionController.php`
    - Implement index action (list sessions)
    - Implement create action (new session form)
    - Implement edit action (edit session form)
    - Implement delete action
    - Implement markComplete action (update status to completed, set completedAt, update streak)
    - Implement markIncomplete action (revert to planned status)
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 18.1, 18.2, 18.5_

  - [ ]* 17.2 Write property test for completion status update
    - **Property 48: Completion status update**
    - **Validates: Requirements 18.1, 18.2**

  - [ ]* 17.3 Write property test for completion triggers streak
    - **Property 49: Completion triggers streak update**
    - **Validates: Requirements 18.4**

  - [ ]* 17.4 Write property test for completion round-trip
    - **Property 50: Completion round-trip**
    - **Validates: Requirements 18.5**

- [x] 18. Create controllers - Analytics dashboard
  - [x] 18.1 Create AnalyticsController
    - Create `src/Controller/StudySession/AnalyticsController.php`
    - Implement dashboard action (display metrics and charts)
    - Calculate total study time, total XP, current streak for current week
    - Prepare data for Chart.js (study time by course, XP over time)
    - Support time range filter (week, month, year)
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8_

  - [ ]* 18.2 Write property test for chart data structure
    - **Property 8: Chart data structure validity**
    - **Validates: Requirements 2.4, 2.5**

  - [ ]* 18.3 Write property test for calendar data completeness
    - **Property 14: Calendar data completeness**
    - **Validates: Requirements 4.1**

- [x] 19. Create controllers - Notes management
  - [x] 19.1 Create NoteController
    - Create `src/Controller/StudySession/NoteController.php`
    - Implement create action (add note to session)
    - Implement edit action (update note content, preserve createdAt)
    - Implement delete action
    - Implement search action (search notes by keyword)
    - _Requirements: 15.1, 15.2, 15.3, 15.5_

  - [ ]* 19.2 Write property test for note timestamp preservation
    - **Property 42: Note timestamp preservation**
    - **Validates: Requirements 15.1, 15.2**

  - [ ]* 19.3 Write property test for note deletion
    - **Property 43: Note deletion**
    - **Validates: Requirements 15.3**

  - [ ]* 19.4 Write property test for note search accuracy
    - **Property 44: Note search accuracy**
    - **Validates: Requirements 15.5**

- [x] 20. Create controllers - Resource management
  - [x] 20.1 Create ResourceController
    - Create `src/Controller/StudySession/ResourceController.php`
    - Implement upload action (handle PDF upload via ResourceManager)
    - Implement download action (serve PDF with correct headers)
    - Implement delete action (remove PDF via ResourceManager)
    - _Requirements: 14.1, 14.2, 14.3, 14.5, 14.6_

  - [ ]* 20.2 Write property test for resource retrieval
    - **Property 40: Resource retrieval with session**
    - **Validates: Requirements 14.4**

  - [ ]* 20.3 Write property test for PDF download headers
    - **Property 41: PDF download headers**
    - **Validates: Requirements 14.6**

- [x] 21. Create controllers - Tag management
  - [x] 21.1 Create TagController
    - Create `src/Controller/StudySession/TagController.php`
    - Implement index action (list all tags with usage counts)
    - Implement create action (create new tag)
    - Implement delete action (remove tag, preserve sessions)
    - Implement filter action (filter sessions by tag)
    - _Requirements: 16.1, 16.2, 16.3, 16.4, 16.5_

  - [ ]* 21.2 Write property test for tag-session association
    - **Property 45: Tag-session association**
    - **Validates: Requirements 16.1, 16.2**

  - [ ]* 21.3 Write property test for tag deletion preserves sessions
    - **Property 46: Tag deletion preserves sessions**
    - **Validates: Requirements 16.4**

  - [ ]* 21.4 Write property test for tag usage counts
    - **Property 47: Tag usage counts**
    - **Validates: Requirements 16.5**

- [ ] 22. Create controllers - External integrations
  - [x] 22.1 Create IntegrationController
    - Create `src/Controller/StudySession/IntegrationController.php`
    - Implement youtubeSearch action (search videos via YouTubeApiClient)
    - Implement wikipediaSearch action (search articles via WikipediaApiClient)
    - Implement weatherSuggestions action (get study suggestions via WeatherApiClient)
    - Implement aiRecommendations action (get recommendations via AIRecommendationService)
    - Implement summarizeNotes action (summarize notes via AIRecommendationService)
    - Implement generateQuiz action (generate quiz via AIRecommendationService)
    - _Requirements: 6.1, 6.2, 7.1, 7.2, 8.1, 8.2, 9.1, 9.2, 10.1, 11.1_

  - [ ]* 22.2 Write property test for API response parsing
    - **Property 22: API response parsing**
    - **Validates: Requirements 6.2, 7.2, 8.5**

  - [ ]* 22.3 Write property test for AI recommendation data
    - **Property 28: AI recommendation data analysis**
    - **Validates: Requirements 9.1, 9.2**

- [x] 23. Create controllers - Calendar
  - [x] 23.1 Create CalendarController
    - Create `src/Controller/StudySession/CalendarController.php`
    - Implement index action (render FullCalendar with all sessions)
    - Implement updateDateTime action (handle drag-and-drop datetime updates)
    - Implement createFromDate action (create session from calendar date click)
    - Return JSON data for FullCalendar with different colors for planned vs completed
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6_

  - [ ]* 23.2 Write property test for session datetime update
    - **Property 15: Session datetime update**
    - **Validates: Requirements 4.2**

  - [ ]* 23.3 Write property test for session status differentiation
    - **Property 16: Session status differentiation**
    - **Validates: Requirements 4.5**

- [x] 24. Create controllers - Energy tracking
  - [x] 24.1 Create EnergyController
    - Create `src/Controller/StudySession/EnergyController.php`
    - Implement analytics action (display energy patterns chart)
    - Implement recommendations action (suggest optimal study times based on energy)
    - Check for minimum 5 sessions before showing recommendations
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5_

  - [ ]* 24.2 Write property test for energy pattern detection
    - **Property 35: Energy pattern detection**
    - **Validates: Requirements 13.1**

  - [ ]* 24.3 Write property test for energy-based recommendations
    - **Property 36: Energy-based time recommendations**
    - **Validates: Requirements 13.2, 13.3**

  - [ ]* 24.4 Write property test for energy chart data
    - **Property 37: Energy chart data structure**
    - **Validates: Requirements 13.4**

- [ ] 25. Checkpoint - Ensure all controller tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 26. Create Twig templates - Study session views
  - [x] 26.1 Create/update study session list template
    - Create/update `templates/study_session/index.html.twig`
    - Display list of study sessions with filters (by tag, by status)
    - Show session details (course, date, duration, XP, mood, energy, status)
    - Add links to edit, delete, mark complete/incomplete
    - _Requirements: 1.7, 18.1, 18.5_

  - [x] 26.2 Create/update study session form template
    - Create/update `templates/front/study_session/form.html.twig`
    - Render StudySessionType form with all new fields
    - Include tag selection (multi-select)
    - Include mood and energy level dropdowns
    - _Requirements: 1.1, 1.2, 1.3, 1.4_

  - [x] 26.3 Create study session detail template
    - Create `templates/front/study_session/show.html.twig`
    - Display all session details including metadata
    - Show associated notes (ordered by date)
    - Show attached PDF resources with download links
    - Show associated tags
    - Include buttons for edit, delete, mark complete
    - _Requirements: 1.7, 14.4, 15.4, 16.1_

- [x] 27. Create Twig templates - Analytics dashboard
  - [x] 27.1 Create analytics dashboard template
    - Create `templates/front/study_session/analytics.html.twig`
    - Display total study time, total XP, current streak for selected time range
    - Add time range filter (week, month, year)
    - Include Chart.js canvas elements for visualizations
    - Show completion rate percentage
    - Show average study duration
    - _Requirements: 2.1, 2.2, 2.3, 2.6, 2.7, 2.8_

  - [x] 27.2 Add Chart.js JavaScript for analytics
    - Create `public/js/analytics-charts.js`
    - Implement study time by course bar chart
    - Implement XP over time line chart
    - Fetch data from controller via data attributes or AJAX
    - Make charts responsive for mobile
    - _Requirements: 2.4, 2.5, 24.1_

- [x] 28. Create Twig templates - Calendar view
  - [x] 28.1 Create calendar template
    - Create `templates/front/study_session/calendar.html.twig`
    - Include FullCalendar library (CSS and JS)
    - Configure FullCalendar with month/week/day views
    - Use different colors for planned vs completed sessions
    - Enable drag-and-drop for rescheduling
    - Enable click to create new session
    - Enable click on session to view details
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6_

  - [x] 28.2 Add FullCalendar JavaScript integration
    - Create `public/js/calendar.js`
    - Initialize FullCalendar with event data from controller
    - Handle drag-and-drop events (send AJAX to update datetime)
    - Handle date click (open create form modal)
    - Handle event click (open detail modal or navigate to detail page)
    - _Requirements: 4.2, 4.3, 4.4_

- [x] 29. Create Twig templates - Notes management
  - [x] 29.1 Create notes list template
    - Create `templates/front/study_session/notes.html.twig`
    - Display notes for a study session (newest first)
    - Show note content, created date, updated date
    - Add buttons to edit and delete notes
    - Include search form for keyword search
    - _Requirements: 15.4, 15.5_

  - [x] 29.2 Create note form template
    - Create `templates/front/study_session/note_form.html.twig`
    - Render NoteType form with textarea
    - Show character count (max 10,000)
    - _Requirements: 15.1, 15.6_

- [x] 30. Create Twig templates - Resource management
  - [x] 30.1 Create resource upload template
    - Create `templates/front/study_session/resource_upload.html.twig`
    - Render ResourceUploadType form
    - Show file size limit (10MB) and accepted format (PDF)
    - Display validation errors
    - _Requirements: 14.1, 14.3_

  - [x] 30.2 Create resource list template
    - Create `templates/front/study_session/resources.html.twig`
    - Display list of PDF resources for a session
    - Show filename, file size, upload date
    - Add download and delete buttons
    - _Requirements: 14.4, 14.6_

- [x] 31. Create Twig templates - Tag management
  - [x] 31.1 Create tag list template
    - Create `templates/front/study_session/tags.html.twig`
    - Display all tags with usage counts
    - Add button to create new tag
    - Add button to delete tag
    - Add link to filter sessions by tag
    - _Requirements: 16.5_

  - [x] 31.2 Create tag form template
    - Create `templates/front/study_session/tag_form.html.twig`
    - Render TagType form
    - Show character limit (50 chars)
    - Display validation errors (uniqueness)
    - _Requirements: 16.3, 16.6_

- [-] 32. Create Twig templates - External integrations
  - [x] 32.1 Create YouTube search template
    - Create `templates/front/study_session/youtube_search.html.twig`
    - Display search form
    - Show video results (title, thumbnail, channel, view count)
    - Add links to open videos in new tab
    - Show error message if API unavailable
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

  - [x] 32.2 Create Wikipedia search template
    - Create `templates/front/study_session/wikipedia_search.html.twig`
    - Display search form
    - Show article results (title, summary)
    - Add links to open articles in new tab
    - Show error message if API unavailable or no results
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_

  <!-- - [ ] 32.3 Create weather suggestions template
    - Create `templates/study_session/weather_suggestions.html.twig`
    - Display current weather (temperature, conditions)
    - Show study time suggestions based on weather
    - Show error message if API unavailable
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_ -->

  - [x] 32.4 Create AI recommendations template
    - Create `templates/front/study_session/ai_recommendations.html.twig`
    - Display personalized study recommendations
    - Show note summarization results
    - Show generated quiz questions with answer validation
    - Show error messages if AI API unavailable
    - _Requirements: 9.1, 9.3, 10.2, 10.3, 11.2, 11.3, 11.4_

- [x] 33. Create Twig templates - Energy tracking
  - [x] 33.1 Create energy analytics template
    - Create `templates/front/study_session/energy_analytics.html.twig`
    - Display energy patterns/bar chart(Chart.js)
    - Show optimal study time recommendations
    - Show message if insufficient data (<5 sessions)
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5_

  - [x] 33.2 Add Chart.js JavaScript for energy visualization
    - Create `public/js/energy-charts.js`
    - Implement energy levels over time chart
    - Highlight high-energy and low-energy periods
    <!-- - Make chart responsive for mobile -->
    - _Requirements: 13.4_

- [x] 34. Implement Pomodoro timer frontend
  - [x] 34.1 Create Pomodoro timer template
    - Create `templates/front/study_session/pomodoro.html.twig`
    - Display timer countdown (25 minutes)
    - Add start, pause, resume buttons
    - Show completed Pomodoro count
    - Display break suggestions (5 min or 15 min)
    - Play notification sound when timer completes
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_

  - [x] 34.2 Add Pomodoro timer JavaScript
    - Create `public/js/pomodoro-timer.js`
    - Implement countdown logic
    - Handle pause/resume state
    - Send AJAX request to update pomodoro count on completion
    - Show break duration based on completed count
    - _Requirements: 12.1, 12.4, 12.5, 12.6_

<!-- - [ ] 35. Implement focus mode
  - [ ] 35.1 Add focus mode toggle to study session view
    - Update `templates/front/study_session/show.html.twig`
    - Add focus mode toggle button
    - _Requirements: 17.1_

  - [ ] 35.2 Add focus mode JavaScript
    - Create `public/js/focus-mode.js`
    - Hide non-essential UI elements when focus mode enabled
    - Show only timer, session details, and notes interface
    - Restore UI elements when focus mode disabled
    - Auto-disable focus mode on page navigation
    - Show minimal break notifications during focus mode
    - _Requirements: 17.1, 17.2, 17.3, 17.4, 17.5_

- [ ] 36. Implement mobile responsiveness
  - [ ] 36.1 Add responsive CSS for all templates
    - Create `public/css/study-session-responsive.css`
    - Add media queries for mobile devices
    - Optimize calendar for mobile (default to day/week view)
    - Scale charts to fit mobile screens
    - Optimize forms for mobile (appropriate input types)
    - Ensure touch gestures work (taps, swipes)
    - _Requirements: 24.1, 24.2, 24.3, 24.4, 24.5_ -->

<!-- - [ ] 37. Implement input validation and security
  - [ ] 37.1 Add validation constraints to entities
    - Add validation annotations to StudySession entity (required fields, date ranges)
    - Add validation annotations to Tag entity (unique name, max 50 chars)
    - Add validation annotations to Note entity (max 10,000 chars)
    - Add validation annotations to Resource entity (file type, size)
    - _Requirements: 21.1, 21.4_ -->

  - [ ] 37.2 Add XSS prevention for user content
    - Configure Twig autoescape for all templates
    - Sanitize note content before storage (strip HTML/script tags)
    - Sanitize tag names before storage
    - _Requirements: 21.3_

  - [ ]* 37.3 Write property test for XSS prevention
    - **Property 53: XSS prevention**
    - **Validates: Requirements 21.3**

- [ ] 38. Checkpoint - Ensure all frontend tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 39. Create background job commands
  - [x] 39.1 Create SendSessionRemindersCommand
    - Create `src/Command/StudySession/SendSessionRemindersCommand.php`
    - Query for sessions scheduled in next 30 minutes
    - Queue email jobs via NotificationService
    - Schedule to run every 5 minutes via cron
    - _Requirements: 5.1, 23.1_

  - [x] 39.2 Create SendWeeklyReportsCommand
    - Create `src/Command/StudySession/SendWeeklyReportsCommand.php`
    - Query all users with completed sessions in past week
    - Generate and send weekly progress reports via NotificationService
    - Schedule to run every Sunday at 23:59 via cron
    - _Requirements: 5.3, 5.4, 23.3_

  - [x] 39.3 Create CheckStreaksCommand
    - Create `src/Command/StudySession/CheckStreaksCommand.php`
    - Query all users with active streaks
    - Check for 24-hour gaps and reset streaks via StreakService
    - Schedule to run daily at midnight via cron
    - _Requirements: 3.2, 23.3_

  - [x] 39.4 Create CheckAchievementsCommand
    - Create `src/Command/StudySession/CheckAchievementsCommand.php`
    - Query users who reached 7, 30, or 100-day streaks
    - Send achievement notifications via NotificationService
    - Schedule to run daily at midnight via cron
    - _Requirements: 5.5, 23.3_

  - [ ]* 39.5 Write property tests for background job processing
    - **Property 60: Email job queuing**
    - **Property 61: Async processing threshold**
    - **Property 62: Job retry with backoff**
    - **Property 63: Job failure notification**
    - **Validates: Requirements 23.1, 23.2, 23.4, 23.5**

- [x] 40. Configure Symfony Messenger for async processing
  - [x] 40.1 Configure message transport
    - Update `config/packages/messenger.yaml`
    - Configure async transport (Doctrine, Redis, or RabbitMQ)
    - Configure routing for email messages
    - Configure retry strategy (3 retries with exponential backoff)
    - _Requirements: 23.1, 23.4_

  - [x] 40.2 Create message handlers
    - Create `src/MessageHandler/StudySession/SendEmailMessageHandler.php`
    - Handle email sending asynchronously
    - Log failures after all retries exhausted
    - _Requirements: 23.1, 23.5_

- [ ] 41. Configure environment variables
  - [x] 41.1 Add API keys to .env file
    - Add YOUTUBE_API_KEY
    <!-- - Add OPENWEATHER_API_KEY -->
    - Add OPENAI_API_KEY (or GEMINI_API_KEY)
    - Add UPLOAD_DIRECTORY path
    - Document all variables in .env.example
    - _Requirements: 6.1, 8.1, 9.2_

- [x] 42. Create database indexes for performance
  - [x] 42.1 Add indexes to improve query performance
    - Add index on study_session.status
    - Add index on study_session.scheduled_at
    - Add index on study_session.completed_at
    - Add index on note.study_session_id
    - Add index on resource.study_session_id
    - Add index on tag.name
    - Create migration for indexes
    - _Requirements: Performance optimization_

- [ ] 43. Implement rate limiting for API calls
  - [ ] 43.1 Add rate limiting to API client services
    - Update YouTubeApiClient with rate limit handling
    - Update WikipediaApiClient with rate limit handling
    - Update WeatherApiClient with rate limit handling
    - Update AIRecommendationService with rate limit handling
    - Implement cache fallback when rate limited
    - _Requirements: 20.4, 22.2_

  - [ ]* 43.2 Write property test for rate limit caching
    - **Property 26: Rate limit caching**
    - **Validates: Requirements 20.4**

- [ ] 44. Add logging for debugging and monitoring
  - [ ] 44.1 Add logging to all services
    - Add logger to AnalyticsService (log calculation errors)
    - Add logger to StreakService (log streak updates)
    - Add logger to NotificationService (log email sending)
    - Add logger to all API clients (log requests, responses, errors)
    - Add logger to ResourceManager (log file operations)
    - Configure log channels in `config/packages/monolog.yaml`
    - _Requirements: 20.1, 20.5_

- [ ] 45. Create user notification preferences
  - [ ] 45.1 Add notification preferences to User entity
    - Add emailNotificationsEnabled field (boolean, default true)
    - Add migration for new field
    - _Requirements: 5.6_

  - [ ] 45.2 Create notification preferences form and controller
    - Create NotificationPreferencesType form
    - Add preferences action to user settings controller
    - Create preferences template
    - _Requirements: 5.6_

- [ ] 46. Final checkpoint - Integration testing
  - [ ] 46.1 Test complete workflows
    - Test: Create session → Add notes → Upload PDF → Mark complete → Check streak
    - Test: View analytics → Filter by time range → View charts
    - Test: Use calendar → Drag session to new date → View updated session
    - Test: Search YouTube videos → Search Wikipedia
    - Test: Get AI recommendations → Summarize notes → Generate quiz
    - Test: Start Pomodoro timer → Complete interval → Check count
    - Test: Enable focus mode → Verify UI changes → Disable focus mode
    - Ensure all tests pass, ask the user if questions arise.

  - [ ]* 46.2 Write integration tests for key workflows
    - Test session creation to completion workflow
    - Test analytics calculation workflow
    - Test calendar integration workflow
    - Test external API integration workflow
    - _Requirements: All requirements_

- [x] 47. Documentation and deployment preparation
  - [x] 47.1 Update README with new features
    - Document all new features and capabilities
    - Document all routes i'm gonna access to see if it works
    - Document required environment variables
    - Document cron job setup for background commands
    - Document API key setup instructions
    - _Requirements: All requirements_

  - [x] 47.2 Create deployment checklist
    - Document database migration steps
    - Document environment variable configuration
    - Document file upload directory setup
    - Document Symfony Messenger transport configuration
    - Document cron job configuration
    - _Requirements: All requirements_

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation at key milestones
- Property tests validate universal correctness properties (minimum 100 iterations each)
- Unit tests validate specific examples, edge cases, and error conditions
- All new files should be placed in StudySession folders as specified
- Templates should go in study_session, course, or planning folders as appropriate
- Use Eris library for property-based testing in PHP
- Configure each property test with tag: `@group Feature: study-session-enhancement, Property {number}: {property_text}`
