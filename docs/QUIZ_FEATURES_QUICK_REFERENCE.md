# Quiz System - Quick Reference Guide

## All Quiz Features at a Glance

### 1. Quiz Reporting System ✅
**What:** Users can report inappropriate quizzes
**Where:** Front-end quiz arcade → Report button on each quiz
**Admin:** `/admin/quiz/reports` - View and manage reports
**Notifications:** Email sent to all admins when quiz is reported

### 2. Email Notifications ✅
**What:** Automated email alerts for quiz reports
**Configuration:** Uses Gmail SMTP (already configured in `.env`)
**Recipients:** All users with ROLE_ADMIN
**Template:** `templates/emails/quiz_report_notification.html.twig`

### 3. Admin Notification Badges ✅
**What:** Visual indicators for pending reports
**Where:** 
- Sidebar: Pulsing badge on "Quiz Reports" menu item
- Navbar: Bell icon with dropdown showing report count
**Updates:** Real-time count via Twig extension

### 4. Quiz Filtering & Sorting ✅
**What:** Search and organize quizzes
**Filters:**
- Text search (title/description)
- Min/max question count
**Sorting:**
- By title, ID, or question count
- Ascending or descending
**Where:** Both admin and front-end quiz lists

### 5. Pagination (NEW) ✅
**What:** Navigate through large quiz lists
**Admin:** 12 quizzes per page (3x4 grid)
**Front-end:** 9 quizzes per page (3x3 grid)
**Features:**
- Page numbers with prev/next buttons
- Shows "X to Y of Z results"
- Preserves filters and sorting

### 6. Question Images (NEW) ✅
**What:** Upload images for quiz questions
**Supported:** JPEG, PNG, GIF, WebP (max 2MB)
**Where:** Admin → Quiz → Manage Questions → Add/Edit Question
**Display:** Images shown during quiz gameplay
**Optional:** Questions work fine without images

## Quick Commands

```bash
# Test pagination
php bin/console app:test-pagination

# Test quiz filtering
php bin/console app:test-quiz-filtering

# Test email notifications
php bin/console app:test-quiz-report-notification

# Check OAuth configuration
php bin/console app:check-oauth-config

# Update database schema
php bin/console doctrine:schema:update --force

# Clear cache
php bin/console cache:clear
```

## Directory Structure

```
src/
├── Controller/
│   ├── Admin/Quiz/
│   │   ├── QuizController.php          # Admin quiz management
│   │   └── QuizReportController.php    # Admin report management
│   └── Front/Quiz/
│       ├── QuizGameController.php      # Front-end quiz arcade & gameplay
│       └── QuizReportController.php    # User report submission
├── Entity/
│   ├── Quiz.php                        # Quiz entity
│   └── Quiz/
│       ├── Question.php                # Question entity (with images)
│       ├── Choice.php                  # Answer choice entity
│       └── QuizReport.php              # Report entity
├── Form/
│   ├── Admin/Quiz/
│   │   ├── QuizType.php                # Quiz form
│   │   └── QuizFilterType.php          # Filter form
│   └── Quiz/
│       ├── QuestionType.php            # Question form (with image upload)
│       ├── AnswerType.php              # Answer choice form
│       └── QuizReportType.php          # Report form
├── Repository/
│   ├── QuizRepository.php              # Quiz queries (with filtering)
│   └── Quiz/
│       └── QuizReportRepository.php    # Report queries
└── Service/
    └── QuizReportNotificationService.php  # Email notifications

templates/
├── admin/
│   └── quiz/
│       ├── index.html.twig             # Quiz list (paginated)
│       ├── show.html.twig              # Manage questions
│       └── reports/
│           ├── index.html.twig         # Report list
│           └── show.html.twig          # Report details
├── front/quiz/
│   ├── game/
│   │   ├── index.html.twig             # Quiz arcade (paginated)
│   │   └── play.html.twig              # Gameplay (with images)
│   └── report.html.twig                # Report form
├── emails/
│   └── quiz_report_notification.html.twig  # Email template
└── pagination/
    └── custom_pagination.html.twig     # Pagination UI

public/uploads/
├── questions/                          # Question images
├── rewards/                            # Reward images
└── avatars/                            # User avatars

docs/
├── QUIZ_REPORTING_SYSTEM.md            # Report system docs
├── QUIZ_FILTERING_SORTING_SYSTEM.md    # Filter/sort docs
└── QUIZ_PAGINATION_AND_IMAGES.md       # Pagination & images docs
```

## Configuration Files

```
config/packages/
├── knp_paginator.yaml                  # Pagination config
├── vich_uploader.yaml                  # Image upload config
├── mailer.yaml                         # Email config
└── security.yaml                       # User roles

.env                                    # Environment variables
├── MAILER_DSN                          # Gmail SMTP
├── OPENAI_API_KEY                      # AI features
├── GEMINI_API_KEY                      # AI features
└── YOUTUBE_API_KEY                     # YouTube integration
```

## User Roles

- **ROLE_USER** - Can play quizzes, report quizzes
- **ROLE_ADMIN** - Full access to quiz management, reports
- **ROLE_TUTOR** - Can manage quizzes (same as admin for quizzes)

## API Keys Status

✅ **Active:**
- Gmail (MAILER_DSN) - Email notifications
- YouTube API - Video integration
- OpenAI API - AI recommendations
- Gemini API - AI features

❌ **Commented Out (Unused):**
- OpenWeather API
- Google OAuth
- LinkedIn OAuth

## Statistics Dashboard

Admin quiz index shows:
- Total Quizzes
- Average Questions per Quiz
- Min Questions in any Quiz
- Max Questions in any Quiz

## Common Tasks

### Create a Quiz
1. Admin → Quiz Manager → Create New Quiz
2. Fill in title and description
3. Save → Manage Questions
4. Add questions with choices
5. Optionally add images to questions

### Handle a Report
1. Admin → Quiz Reports (or click notification badge)
2. Click on a report to view details
3. Choose action:
   - Resolve (mark as handled)
   - Dismiss (not an issue)
   - Delete Quiz (if inappropriate)

### Filter Quizzes
1. Go to quiz list (admin or front-end)
2. Use filter form:
   - Search by text
   - Set min/max questions
   - Choose sort field and order
3. Click "Apply Filters"
4. Navigate pages - filters persist

### Add Image to Question
1. Admin → Quiz → Manage Questions
2. Create/Edit question
3. Scroll to "Question Image (Optional)"
4. Click "Choose File" and select image
5. Save - image appears in gameplay

## Troubleshooting

**Pagination not showing?**
- Need 10+ quizzes to see pagination (admin)
- Need 10+ quizzes to see pagination (front-end)

**Images not uploading?**
- Check file size (max 2MB)
- Check file type (JPEG, PNG, GIF, WebP only)
- Verify `public/uploads/questions/` is writable

**Emails not sending?**
- Check `.env` has correct Gmail credentials
- Verify Gmail app password is valid
- Check spam folder

**Filters not working?**
- Clear browser cache
- Clear Symfony cache: `php bin/console cache:clear`
- Check database has quizzes matching criteria

## Performance Tips

1. **Pagination** automatically improves performance
2. **Images** are optimized with SmartUniqueNamer
3. **Filters** use efficient database queries
4. **Caching** is enabled for production

## Security Features

1. **CSRF Protection** on all forms
2. **File Validation** for image uploads
3. **Role-Based Access** for admin features
4. **SQL Injection Prevention** via Doctrine ORM
5. **XSS Protection** via Twig auto-escaping

## Browser Support

- Chrome ✅
- Firefox ✅
- Safari ✅
- Edge ✅
- Mobile browsers ✅

## Responsive Design

All quiz features work on:
- Desktop (1920px+)
- Laptop (1366px)
- Tablet (768px)
- Mobile (375px)

## Future Enhancements

Potential additions:
- Quiz categories/tags
- Quiz difficulty levels
- Time limits for quizzes
- Leaderboards per quiz
- Quiz analytics dashboard
- Question pools
- Random question selection
- Quiz templates
- Bulk question import
- Question bank sharing

## Support

For issues or questions:
1. Check documentation in `docs/` folder
2. Review this quick reference
3. Test with provided commands
4. Check Symfony logs: `var/log/`

---

**Last Updated:** February 21, 2026
**Version:** 2.0 (with Pagination & Images)
