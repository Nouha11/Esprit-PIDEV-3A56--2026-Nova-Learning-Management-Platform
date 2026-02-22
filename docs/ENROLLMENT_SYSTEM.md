# Course Enrollment System

## Overview

The enrollment system allows students to request enrollment in courses, and tutors to approve or reject those requests. Only enrolled students can start course sessions.

## Features

### For Students

1. **Request Enrollment**: Students can request enrollment in any published course
2. **View Status**: Students can see if their request is pending
3. **Start Course**: Once approved, students can start the course session

### For Tutors

1. **View Requests**: Tutors can view all pending enrollment requests for their courses
2. **Approve/Reject**: Tutors can approve or reject enrollment requests
3. **Manage Access**: Only approved students can access course content

## User Flow

### Student Enrollment Flow

1. Student browses courses on the course index page
2. For courses they're not enrolled in, they see a "Request Enrollment" button
3. Student clicks the button to submit an enrollment request
4. The button changes to "Request Pending" (disabled)
5. Once the tutor approves, the student sees "Start Course" and "Plan Session" buttons

### Tutor Approval Flow

1. Tutor receives enrollment requests for their courses
2. Tutor navigates to "Enrollment Requests" from the profile dropdown menu
3. Tutor reviews student information and optional message
4. Tutor clicks "Approve" or "Reject"
5. Student is notified via flash message on next login

## Database Schema

### enrollment_requests Table

- `id`: Primary key
- `student_id`: Foreign key to user table
- `course_id`: Foreign key to course table
- `status`: PENDING, APPROVED, or REJECTED
- `requested_at`: Timestamp of request
- `responded_at`: Timestamp of tutor response
- `responded_by_id`: Foreign key to user (tutor) who responded
- `message`: Optional message from student

## Routes

### Student Routes

- `POST /enrollment/request/{id}` - Request enrollment in a course
  - Route name: `enrollment_request`
  - Access: ROLE_STUDENT

### Tutor Routes

- `GET /enrollment/requests` - View all pending requests
  - Route name: `enrollment_requests`
  - Access: ROLE_TUTOR

- `POST /enrollment/approve/{id}` - Approve an enrollment request
  - Route name: `enrollment_approve`
  - Access: ROLE_TUTOR

- `POST /enrollment/reject/{id}` - Reject an enrollment request
  - Route name: `enrollment_reject`
  - Access: ROLE_TUTOR

## Services

### EnrollmentService

Located at: `src/Service/StudySession/EnrollmentService.php`

**Methods:**

- `isEnrolled(User $user, Course $course): bool` - Check if student is enrolled
- `getEnrolledCourses(User $user): Collection` - Get all enrolled courses
- `requestEnrollment(User $student, Course $course, ?string $message): EnrollmentRequest` - Create enrollment request
- `approveEnrollment(EnrollmentRequest $request, User $approver): void` - Approve request and enroll student
- `rejectEnrollment(EnrollmentRequest $request, User $rejector): void` - Reject request
- `getPendingRequest(User $student, Course $course): ?EnrollmentRequest` - Get pending request
- `getPendingRequestsForCourse(Course $course): array` - Get all pending requests for a course
- `getPendingRequestsForTutor(User $tutor): array` - Get all pending requests for tutor's courses

## Templates

- `templates/front/course/index.html.twig` - Course listing with enrollment buttons
- `templates/front/enrollment/requests.html.twig` - Tutor's enrollment request management page

## Migration

Run the migration to create the enrollment_requests table:

```bash
php bin/console doctrine:migrations:migrate
```

## Security

- Students can only request enrollment for themselves
- Tutors can only approve/reject requests for their own courses
- Enrollment status is verified before allowing course session access
- Duplicate requests are prevented (one pending request per student per course)

## Future Enhancements

- Email notifications for enrollment requests and responses
- Bulk approval/rejection for tutors
- Enrollment request history for students
- Automatic enrollment for certain courses
- Enrollment capacity limits
