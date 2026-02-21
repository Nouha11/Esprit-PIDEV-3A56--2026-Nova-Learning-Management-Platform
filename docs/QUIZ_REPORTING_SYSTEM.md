# Quiz Reporting System

## Overview
A comprehensive reporting system that allows users to report problematic quizzes and enables admins to review and take action on those reports.

## Features

### User Features
- Report any quiz with a specific reason
- Provide additional details about the issue
- Predefined report reasons:
  - Incorrect Information
  - Inappropriate Content
  - Duplicate Quiz
  - Poor Quality
  - Other
- Automatic confirmation message after submission

### Admin Features
- **Email Notifications**: Receive instant email alerts when a quiz is reported
- View all pending and resolved reports
- Review detailed report information
- Preview the reported quiz
- Take actions on reports:
  - Mark as Resolved
  - Dismiss Report
  - Edit the Quiz
  - Delete the Quiz
- Add admin notes to reports
- Track who resolved each report and when

### Notification System
- Automatic email notifications sent to all active admins
- Professional HTML email template
- Includes:
  - Quiz title and details
  - Reporter information
  - Reason for report
  - Additional description (if provided)
  - Direct link to review the report
  - Timestamp of report submission
- Logging of all notification attempts

## Database Schema

### QuizReport Entity
- `id` - Primary key
- `quiz` - Reference to the reported Quiz
- `reportedBy` - User who submitted the report
- `reason` - Selected reason for the report
- `description` - Optional additional details
- `status` - Current status (pending, resolved, dismissed)
- `createdAt` - When the report was submitted
- `resolvedAt` - When the report was handled
- `resolvedBy` - Admin who handled the report
- `adminNotes` - Internal notes from the admin

## Routes

### Front-End (User)
- `GET/POST /game/quiz/report/{id}` - Report a quiz

### Admin
- `GET /admin/quiz/reports` - List all reports
- `GET /admin/quiz/reports/{id}` - View report details
- `POST /admin/quiz/reports/{id}/resolve` - Resolve or dismiss a report
- `POST /admin/quiz/reports/{id}/delete-quiz` - Delete the reported quiz

## Files Created

### Entities
- `src/Entity/Quiz/QuizReport.php`

### Controllers
- `src/Controller/Front/Quiz/QuizReportController.php`
- `src/Controller/Admin/Quiz/QuizReportController.php`

### Forms
- `src/Form/Quiz/QuizReportType.php`

### Repositories
- `src/Repository/Quiz/QuizReportRepository.php`

### Services
- `src/Service/QuizReportNotificationService.php` - Handles email notifications to admins

### Templates
- `templates/front/quiz/report.html.twig` - User report form
- `templates/admin/quiz/reports/index.html.twig` - Admin reports list
- `templates/admin/quiz/reports/show.html.twig` - Admin report details
- `templates/emails/quiz_report_notification.html.twig` - Email notification template

### Migrations
- `migrations/Version20260220221727.php` - Database migration for quiz_report table

## Usage

### For Users
1. Navigate to the quiz arcade page
2. Click the "Report" link under any quiz
3. Select a reason and optionally provide details
4. Submit the report

### For Admins
1. Go to Quiz Manager
2. Click the "Reports" button
3. Review pending reports
4. Click "Review" on any report
5. Choose an action:
   - Add admin notes
   - Mark as Resolved
   - Dismiss the report
   - Edit the quiz
   - Delete the quiz

## Security
- Only authenticated users can submit reports
- Only admins and tutors can view and manage reports
- CSRF protection on all forms
- Proper authorization checks on all routes
- Email notifications only sent to active admin accounts

## Configuration

### Email Settings
The notification email sender is configured in `config/services.yaml`:
```yaml
parameters:
    admin_notification_email: 'oussamastudy2@gmail.com'
```

### Mailer Configuration
Ensure your `.env` file has the correct mailer DSN:
```
MAILER_DSN=gmail://your-email@gmail.com:your-app-password@default
```

## Technical Details

### Notification Flow
1. User submits a quiz report
2. Report is saved to database
3. `QuizReportNotificationService` is triggered
4. Service queries all active admin users
5. Email is sent to each admin with report details
6. Success/failure is logged for monitoring
7. User sees confirmation message
