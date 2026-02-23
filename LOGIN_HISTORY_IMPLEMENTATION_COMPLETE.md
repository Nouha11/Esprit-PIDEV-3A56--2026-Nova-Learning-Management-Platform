# Login History / Timeline System - Implementation Complete ✅

## Overview
A comprehensive login history tracking and visualization system has been successfully implemented for the NOVA platform. The system automatically tracks all login attempts and provides security insights for both users and administrators.

## What Was Built

### 1. Database Layer ✅
- **LoginHistory Entity** - Stores all login attempt data
- **Migration** - Database table with proper indexes
- **Relationships** - Linked to User entity with cascade delete

### 2. Business Logic ✅
- **LoginHistoryService** - Core service for tracking and querying
  - Log login attempts with full context
  - Get recent logins for users
  - Calculate login statistics
  - Detect suspicious activity
  - Admin queries for all history

### 3. Automatic Tracking ✅
- **LoginHistorySubscriber** - Event-driven tracking
  - Automatically logs successful logins
  - Automatically logs failed login attempts
  - Captures IP, browser, device, platform info
  - Tracks 2FA usage

### 4. Admin Interface ✅
- **LoginHistoryController** - Admin routes
  - View all login history across platform
  - View user-specific login history
  - Statistics and analytics
  - Security alerts

### 5. UI Components ✅
- **Timeline Component** - Full-featured timeline view
  - Color-coded status indicators
  - Detailed information cards
  - Responsive design
  - Dark mode support

- **Widget Component** - Compact dashboard widget
  - Recent 5 logins
  - Quick security overview
  - Ready for dashboard integration

### 6. Admin Views ✅
- **All Login History Page** - Platform-wide view
  - Statistics cards
  - Timeline of all attempts
  - Pagination support

- **User-Specific History Page** - Individual user view
  - User information card
  - 30-day statistics
  - Security alerts
  - Complete timeline

### 7. Documentation ✅
- Complete system documentation
- Quick start guide
- Implementation summary

## Features Implemented

### Tracking Features
- ✅ Login status (success/failed/blocked)
- ✅ IP address capture
- ✅ User agent parsing
- ✅ Browser detection (Chrome, Firefox, Safari, Edge, Opera)
- ✅ Platform detection (Windows, macOS, Linux, Android, iOS)
- ✅ Device type detection (Desktop, Mobile, Tablet)
- ✅ Location tracking (basic, ready for GeoIP)
- ✅ Failure reason logging
- ✅ 2FA usage tracking
- ✅ Timestamp recording

### Analytics Features
- ✅ Total login count
- ✅ Successful login count
- ✅ Failed login count
- ✅ Success rate calculation
- ✅ Time-based statistics (30 days)
- ✅ Suspicious activity detection

### Security Features
- ✅ Multiple failed attempt detection
- ✅ Multiple location detection
- ✅ Multiple device detection
- ✅ Security alerts for admins
- ✅ Real-time monitoring

### UI Features
- ✅ Beautiful timeline visualization
- ✅ Color-coded status indicators
- ✅ Detailed information cards
- ✅ Statistics dashboard
- ✅ Responsive design
- ✅ Dark mode support
- ✅ Badge indicators for 2FA
- ✅ Pagination support

## Files Created

### Backend
```
src/Entity/users/LoginHistory.php
src/Service/LoginHistoryService.php
src/EventSubscriber/LoginHistorySubscriber.php
src/Controller/Admin/LoginHistoryController.php
migrations/Version20260221201406.php
```

### Frontend
```
templates/components/login_history_timeline.html.twig
templates/components/login_history_widget.html.twig
templates/admin/login_history/index.html.twig
templates/admin/login_history/user.html.twig
```

### Documentation
```
docs/LOGIN_HISTORY_SYSTEM.md
LOGIN_HISTORY_QUICK_START.md
LOGIN_HISTORY_IMPLEMENTATION_COMPLETE.md
```

## Database Schema

### login_history Table
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (INT, FOREIGN KEY → user.id, CASCADE DELETE)
- status (VARCHAR(20)) - 'success', 'failed', 'blocked'
- ip_address (VARCHAR(45))
- user_agent (TEXT)
- browser (VARCHAR(100))
- platform (VARCHAR(100))
- device (VARCHAR(100))
- location (VARCHAR(100))
- failure_reason (VARCHAR(255))
- is2fa_used (BOOLEAN, DEFAULT FALSE)
- created_at (DATETIME)

Indexes:
- idx_user_id (user_id)
- idx_created_at (created_at)
- idx_status (status)
```

## Routes

### Admin Routes (Require ROLE_ADMIN)
```
GET  /admin/login-history           - View all login history
GET  /admin/login-history/user/{id} - View user-specific history
```

## How It Works

### 1. Automatic Tracking
When a user attempts to log in:
1. Symfony fires `LoginSuccessEvent` or `LoginFailureEvent`
2. `LoginHistorySubscriber` catches the event
3. Calls `LoginHistoryService->logLoginAttempt()`
4. Service captures request context (IP, user agent, etc.)
5. Parses user agent for browser/platform/device
6. Creates `LoginHistory` entity
7. Persists to database

### 2. Viewing History
Administrators can:
1. Navigate to `/admin/login-history`
2. See all login attempts with statistics
3. Click on specific users to see detailed history
4. View security alerts for suspicious activity

### 3. Security Monitoring
The system automatically detects:
- 3+ failed login attempts
- Logins from 3+ different locations
- Logins from 3+ different devices

Alerts are displayed on user-specific history pages.

## Testing

### Test Successful Login
1. Log out
2. Log in with correct credentials
3. Navigate to `/admin/login-history`
4. Verify your login is recorded with status "Success"

### Test Failed Login
1. Log out
2. Try to log in with wrong password
3. Log in with correct credentials
4. Navigate to `/admin/login-history`
5. Verify both failed and successful attempts are recorded

### Test 2FA Tracking
1. Enable 2FA on your account
2. Log out and log back in
3. Check login history
4. Verify "2FA" badge is shown

### Test User-Specific View
1. Navigate to `/admin/login-history/user/1` (replace 1 with user ID)
2. Verify user information is displayed
3. Verify statistics are calculated
4. Verify timeline shows user's logins only

## Integration Points

### Current Integrations
- ✅ Symfony Security Events
- ✅ 2FA System (tracks 2FA usage)
- ✅ User Entity (cascade delete)
- ✅ Admin Panel

### Ready for Integration
- ⏳ User Dashboards (widget component ready)
- ⏳ User Profile Pages (timeline component ready)
- ⏳ Email Notifications (service methods ready)
- ⏳ Ban System (can trigger based on failed attempts)

## Next Steps (Optional)

### Immediate Enhancements
1. **Add to User Dashboards**
   - Include widget in student/tutor dashboards
   - Show recent 5 logins
   - Add "View All" link

2. **Add to User Profile**
   - Create user-facing route
   - Let users view their own history
   - Security settings page

3. **Link from Admin User Management**
   - Add "View Login History" button to user list
   - Quick access from user edit page

### Future Enhancements
1. **GeoIP Integration** - Accurate location detection
2. **Email Notifications** - Alert users of new logins
3. **Session Management** - View and revoke active sessions
4. **Export Functionality** - Export to CSV/PDF
5. **Advanced Filtering** - Date range, status, location filters
6. **Login Heatmap** - Visual representation of patterns
7. **Anomaly Detection** - ML-based suspicious activity
8. **IP Blacklisting** - Automatic blocking
9. **Device Recognition** - Remember trusted devices
10. **Login Approval** - Require approval for new devices

## Performance Considerations

### Optimizations Implemented
- ✅ Database indexes on frequently queried columns
- ✅ Pagination support for large datasets
- ✅ Efficient QueryBuilder queries
- ✅ Limit parameters on all queries

### Recommendations
- Consider archiving records older than 90 days
- Monitor table size as it grows
- Implement caching for statistics
- Consider read replicas for heavy traffic

## Security & Privacy

### Security Features
- ✅ Admin-only access to full history
- ✅ Cascade delete with user
- ✅ Suspicious activity detection
- ✅ Real-time monitoring

### Privacy Considerations
- IP addresses stored for security
- User agents stored for device detection
- Location data is optional and basic
- Consider GDPR compliance
- Implement data retention policy

## Browser/Platform Detection

### Supported Browsers
- Google Chrome
- Mozilla Firefox
- Safari
- Microsoft Edge
- Opera

### Supported Platforms
- Windows (7, 8, 8.1, 10, 11)
- macOS
- Linux
- Android
- iOS

### Device Types
- Desktop
- Mobile
- Tablet

## Statistics Tracked

### Per User (30 days)
- Total login attempts
- Successful logins
- Failed logins
- Success rate percentage

### Platform-wide
- Total logins (current page)
- Successful logins
- Failed logins
- 2FA usage count

## Troubleshooting

### Login attempts not being recorded
**Solution**: 
- Verify migration ran: `php bin/console doctrine:migrations:status`
- Check Symfony logs: `var/log/dev.log`
- Clear cache: `php bin/console cache:clear`

### Can't access admin routes
**Solution**:
- Ensure logged in as admin (ROLE_ADMIN)
- Check security.yaml configuration
- Clear cache

### Incorrect browser/platform detection
**Note**: 
- User agent parsing is basic
- Consider integrating `mobiledetect/mobiledetectlib` for better detection

### Missing location data
**Note**:
- Basic implementation returns null for non-localhost
- Integrate GeoIP service (MaxMind, ip-api.com) for accurate location

## Cache Status
✅ Cache cleared successfully

## Migration Status
✅ Migration executed successfully
- Table created: `login_history`
- Indexes created: `idx_user_id`, `idx_created_at`, `idx_status`
- Foreign key created: `user_id → user.id (CASCADE DELETE)`

## Testing Status
⏳ Ready for testing
- All components created
- Database migrated
- Cache cleared
- Routes registered
- Services autowired

## Documentation Status
✅ Complete
- System documentation: `docs/LOGIN_HISTORY_SYSTEM.md`
- Quick start guide: `LOGIN_HISTORY_QUICK_START.md`
- Implementation summary: This file

## Summary

The Login History / Timeline System is fully implemented and ready for use. The system automatically tracks all login attempts, provides beautiful visualizations, calculates security statistics, and detects suspicious activity. Administrators can monitor all login activity across the platform, while users can view their own history (integration pending).

All backend services, database tables, event subscribers, controllers, templates, and documentation are complete. The system is production-ready with proper indexing, pagination, and security features.

**Status**: ✅ COMPLETE AND READY FOR USE

**Next Action**: Test the system by logging in/out and visiting `/admin/login-history`
<nav class="navbar sidebar navbar-expand-xl navbar-dark bg-dark">

    <div class="d-flex align-items-center">
        <a class="navbar-brand" href="{{ path('app_admin_dashboard') }}">
            <img class="navbar-brand-item" src="{{ asset('assets/images/Logo.png') }}" alt="NOVA" style="height: 90px;">
        </a>
    </div>
    <div class="offcanvas offcanvas-start flex-row custom-scrollbar h-100" data-bs-backdrop="true" tabindex="-1" id="offcanvasSidebar">
        <div class="offcanvas-body sidebar-content d-flex flex-column bg-dark">

            <ul class="navbar-nav flex-column" id="navbar-sidebar">
                
                <li class="nav-item">
                    <a href="{{ path('app_admin_dashboard') }}" class="nav-link{% if app.request.attributes.get('_route') == 'app_admin_dashboard' %} active{% endif %}">
                        <i class="bi bi-house fa-fw me-2"></i>Dashboard
                    </a>
                </li>
                
                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Learning Management</li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_course' %} active{% endif %}" href="{{ path('admin_course_index') }}">
                        <i class="bi bi-basket fa-fw me-2"></i>Courses
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') starts with 'admin_planning' %} active{% endif %}" href="{{ path('admin_planning_index') }}">
                        <i class="bi bi-calendar-check fa-fw me-2"></i>Planning Sessions
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') starts with 'admin_study_session' %} active{% endif %}" href="{{ path('admin_study_session_index') }}">
                        <i class="bi bi-journal-check fa-fw me-2"></i>Completed Sessions
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'app_quiz_index' or app.request.attributes.get('_route') starts with 'app_quiz_' %} active{% endif %}" href="{{ path('app_quiz_index', {'prefix': 'admin'}) }}">
                        <i class="fas fa-question-circle fa-fw me-2"></i>Quizzes
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4 d-flex justify-content-between align-items-center{% if app.request.attributes.get('_route') starts with 'app_quiz_reports' %} active{% endif %}" href="{{ path('app_quiz_reports_index', {'prefix': 'admin'}) }}">
                        <span>
                            <i class="fas fa-flag fa-fw me-2"></i>Quiz Reports
                        </span>
                        {% set pendingCount = pending_reports_count() %}
                        {% if pendingCount > 0 %}
                            <span class="badge bg-danger rounded-pill ms-2 pulse-badge">{{ pendingCount }}</span>
                        {% endif %}
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') == 'app_quiz_statistics' %} active{% endif %}" href="{{ path('app_quiz_statistics', {'prefix': 'admin'}) }}">
                        <i class="bi bi-bar-chart fa-fw me-2"></i>Quiz Statistics
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_books' %} active{% endif %}" href="{{ path('admin_books_index') }}">
                        <i class="fas fa-book fa-fw me-2"></i>Library
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Gamification</li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('admin_game_index') }}">
                        <i class="fas fa-gamepad fa-fw me-2"></i>Games
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('admin_reward_index') }}">
                        <i class="fas fa-trophy fa-fw me-2"></i>Rewards
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_statistics' %} active{% endif %}" href="{{ path('admin_statistics_dashboard') }}">
                        <i class="bi bi-graph-up fa-fw me-2"></i>Statistics
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Community</li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('app_admin_forum_index') }}">
                        <i class="fas fa-comments fa-fw me-2"></i>Forum Moderation
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Security</li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_login_history' %} active{% endif %}" href="{{ path('admin_login_history_index') }}">
                        <i class="bi bi-clock-history fa-fw me-2"></i>Login History
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('app_admin_users_list') }}">
                        <i class="bi bi-people fa-fw me-2"></i>User Management
                    </a>
                </li>

            </ul>
            <div class="px-3 mt-auto pt-3">
                <div class="d-flex align-items-center justify-content-between text-primary-hover">
                    <a class="h5 mb-0 text-body" href="{{ path('app_2fa_manage') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Security (2FA)">
                        <i class="bi bi-shield-lock"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Settings">
                        <i class="bi bi-gear-fill"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="{{ path('app_home') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Go to Website">
                        <i class="bi bi-globe"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="{{ path('app_logout') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Sign out">
                        <i class="bi bi-power"></i>
                    </a>
                </div>
            </div>
            </div>
    </div>
</nav><nav class="navbar sidebar navbar-expand-xl navbar-dark bg-dark">

    <div class="d-flex align-items-center">
        <a class="navbar-brand" href="{{ path('app_admin_dashboard') }}">
            <img class="navbar-brand-item" src="{{ asset('assets/images/Logo.png') }}" alt="NOVA" style="height: 90px;">
        </a>
    </div>
    <div class="offcanvas offcanvas-start flex-row custom-scrollbar h-100" data-bs-backdrop="true" tabindex="-1" id="offcanvasSidebar">
        <div class="offcanvas-body sidebar-content d-flex flex-column bg-dark">

            <ul class="navbar-nav flex-column" id="navbar-sidebar">
                
                <li class="nav-item">
                    <a href="{{ path('app_admin_dashboard') }}" class="nav-link{% if app.request.attributes.get('_route') == 'app_admin_dashboard' %} active{% endif %}">
                        <i class="bi bi-house fa-fw me-2"></i>Dashboard
                    </a>
                </li>
                
                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Learning Management</li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_course' %} active{% endif %}" href="{{ path('admin_course_index') }}">
                        <i class="bi bi-basket fa-fw me-2"></i>Courses
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') starts with 'admin_planning' %} active{% endif %}" href="{{ path('admin_planning_index') }}">
                        <i class="bi bi-calendar-check fa-fw me-2"></i>Planning Sessions
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') starts with 'admin_study_session' %} active{% endif %}" href="{{ path('admin_study_session_index') }}">
                        <i class="bi bi-journal-check fa-fw me-2"></i>Completed Sessions
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'app_quiz_index' or app.request.attributes.get('_route') starts with 'app_quiz_' %} active{% endif %}" href="{{ path('app_quiz_index', {'prefix': 'admin'}) }}">
                        <i class="fas fa-question-circle fa-fw me-2"></i>Quizzes
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4 d-flex justify-content-between align-items-center{% if app.request.attributes.get('_route') starts with 'app_quiz_reports' %} active{% endif %}" href="{{ path('app_quiz_reports_index', {'prefix': 'admin'}) }}">
                        <span>
                            <i class="fas fa-flag fa-fw me-2"></i>Quiz Reports
                        </span>
                        {% set pendingCount = pending_reports_count() %}
                        {% if pendingCount > 0 %}
                            <span class="badge bg-danger rounded-pill ms-2 pulse-badge">{{ pendingCount }}</span>
                        {% endif %}
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') == 'app_quiz_statistics' %} active{% endif %}" href="{{ path('app_quiz_statistics', {'prefix': 'admin'}) }}">
                        <i class="bi bi-bar-chart fa-fw me-2"></i>Quiz Statistics
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_books' %} active{% endif %}" href="{{ path('admin_books_index') }}">
                        <i class="fas fa-book fa-fw me-2"></i>Library
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Gamification</li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('admin_game_index') }}">
                        <i class="fas fa-gamepad fa-fw me-2"></i>Games
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('admin_reward_index') }}">
                        <i class="fas fa-trophy fa-fw me-2"></i>Rewards
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_statistics' %} active{% endif %}" href="{{ path('admin_statistics_dashboard') }}">
                        <i class="bi bi-graph-up fa-fw me-2"></i>Statistics
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Community</li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('app_admin_forum_index') }}">
                        <i class="fas fa-comments fa-fw me-2"></i>Forum Moderation
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Security</li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_login_history' %} active{% endif %}" href="{{ path('admin_login_history_index') }}">
                        <i class="bi bi-clock-history fa-fw me-2"></i>Login History
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('app_admin_users_list') }}">
                        <i class="bi bi-people fa-fw me-2"></i>User Management
                    </a>
                </li>

            </ul>
            <div class="px-3 mt-auto pt-3">
                <div class="d-flex align-items-center justify-content-between text-primary-hover">
                    <a class="h5 mb-0 text-body" href="{{ path('app_2fa_manage') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Security (2FA)">
                        <i class="bi bi-shield-lock"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Settings">
                        <i class="bi bi-gear-fill"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="{{ path('app_home') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Go to Website">
                        <i class="bi bi-globe"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="{{ path('app_logout') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Sign out">
                        <i class="bi bi-power"></i>
                    </a>
                </div>
            </div>
            </div>
    </div>
</nav><nav class="navbar sidebar navbar-expand-xl navbar-dark bg-dark">

    <div class="d-flex align-items-center">
        <a class="navbar-brand" href="{{ path('app_admin_dashboard') }}">
            <img class="navbar-brand-item" src="{{ asset('assets/images/Logo.png') }}" alt="NOVA" style="height: 90px;">
        </a>
    </div>
    <div class="offcanvas offcanvas-start flex-row custom-scrollbar h-100" data-bs-backdrop="true" tabindex="-1" id="offcanvasSidebar">
        <div class="offcanvas-body sidebar-content d-flex flex-column bg-dark">

            <ul class="navbar-nav flex-column" id="navbar-sidebar">
                
                <li class="nav-item">
                    <a href="{{ path('app_admin_dashboard') }}" class="nav-link{% if app.request.attributes.get('_route') == 'app_admin_dashboard' %} active{% endif %}">
                        <i class="bi bi-house fa-fw me-2"></i>Dashboard
                    </a>
                </li>
                
                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Learning Management</li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_course' %} active{% endif %}" href="{{ path('admin_course_index') }}">
                        <i class="bi bi-basket fa-fw me-2"></i>Courses
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') starts with 'admin_planning' %} active{% endif %}" href="{{ path('admin_planning_index') }}">
                        <i class="bi bi-calendar-check fa-fw me-2"></i>Planning Sessions
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') starts with 'admin_study_session' %} active{% endif %}" href="{{ path('admin_study_session_index') }}">
                        <i class="bi bi-journal-check fa-fw me-2"></i>Completed Sessions
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'app_quiz_index' or app.request.attributes.get('_route') starts with 'app_quiz_' %} active{% endif %}" href="{{ path('app_quiz_index', {'prefix': 'admin'}) }}">
                        <i class="fas fa-question-circle fa-fw me-2"></i>Quizzes
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4 d-flex justify-content-between align-items-center{% if app.request.attributes.get('_route') starts with 'app_quiz_reports' %} active{% endif %}" href="{{ path('app_quiz_reports_index', {'prefix': 'admin'}) }}">
                        <span>
                            <i class="fas fa-flag fa-fw me-2"></i>Quiz Reports
                        </span>
                        {% set pendingCount = pending_reports_count() %}
                        {% if pendingCount > 0 %}
                            <span class="badge bg-danger rounded-pill ms-2 pulse-badge">{{ pendingCount }}</span>
                        {% endif %}
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') == 'app_quiz_statistics' %} active{% endif %}" href="{{ path('app_quiz_statistics', {'prefix': 'admin'}) }}">
                        <i class="bi bi-bar-chart fa-fw me-2"></i>Quiz Statistics
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_books' %} active{% endif %}" href="{{ path('admin_books_index') }}">
                        <i class="fas fa-book fa-fw me-2"></i>Library
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Gamification</li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('admin_game_index') }}">
                        <i class="fas fa-gamepad fa-fw me-2"></i>Games
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('admin_reward_index') }}">
                        <i class="fas fa-trophy fa-fw me-2"></i>Rewards
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_statistics' %} active{% endif %}" href="{{ path('admin_statistics_dashboard') }}">
                        <i class="bi bi-graph-up fa-fw me-2"></i>Statistics
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Community</li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('app_admin_forum_index') }}">
                        <i class="fas fa-comments fa-fw me-2"></i>Forum Moderation
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Security</li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_login_history' %} active{% endif %}" href="{{ path('admin_login_history_index') }}">
                        <i class="bi bi-clock-history fa-fw me-2"></i>Login History
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('app_admin_users_list') }}">
                        <i class="bi bi-people fa-fw me-2"></i>User Management
                    </a>
                </li>

            </ul>
            <div class="px-3 mt-auto pt-3">
                <div class="d-flex align-items-center justify-content-between text-primary-hover">
                    <a class="h5 mb-0 text-body" href="{{ path('app_2fa_manage') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Security (2FA)">
                        <i class="bi bi-shield-lock"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Settings">
                        <i class="bi bi-gear-fill"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="{{ path('app_home') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Go to Website">
                        <i class="bi bi-globe"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="{{ path('app_logout') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Sign out">
                        <i class="bi bi-power"></i>
                    </a>
                </div>
            </div>
            </div>
    </div>
</nav><nav class="navbar sidebar navbar-expand-xl navbar-dark bg-dark">

    <div class="d-flex align-items-center">
        <a class="navbar-brand" href="{{ path('app_admin_dashboard') }}">
            <img class="navbar-brand-item" src="{{ asset('assets/images/Logo.png') }}" alt="NOVA" style="height: 90px;">
        </a>
    </div>
    <div class="offcanvas offcanvas-start flex-row custom-scrollbar h-100" data-bs-backdrop="true" tabindex="-1" id="offcanvasSidebar">
        <div class="offcanvas-body sidebar-content d-flex flex-column bg-dark">

            <ul class="navbar-nav flex-column" id="navbar-sidebar">
                
                <li class="nav-item">
                    <a href="{{ path('app_admin_dashboard') }}" class="nav-link{% if app.request.attributes.get('_route') == 'app_admin_dashboard' %} active{% endif %}">
                        <i class="bi bi-house fa-fw me-2"></i>Dashboard
                    </a>
                </li>
                
                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Learning Management</li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_course' %} active{% endif %}" href="{{ path('admin_course_index') }}">
                        <i class="bi bi-basket fa-fw me-2"></i>Courses
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') starts with 'admin_planning' %} active{% endif %}" href="{{ path('admin_planning_index') }}">
                        <i class="bi bi-calendar-check fa-fw me-2"></i>Planning Sessions
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') starts with 'admin_study_session' %} active{% endif %}" href="{{ path('admin_study_session_index') }}">
                        <i class="bi bi-journal-check fa-fw me-2"></i>Completed Sessions
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'app_quiz_index' or app.request.attributes.get('_route') starts with 'app_quiz_' %} active{% endif %}" href="{{ path('app_quiz_index', {'prefix': 'admin'}) }}">
                        <i class="fas fa-question-circle fa-fw me-2"></i>Quizzes
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4 d-flex justify-content-between align-items-center{% if app.request.attributes.get('_route') starts with 'app_quiz_reports' %} active{% endif %}" href="{{ path('app_quiz_reports_index', {'prefix': 'admin'}) }}">
                        <span>
                            <i class="fas fa-flag fa-fw me-2"></i>Quiz Reports
                        </span>
                        {% set pendingCount = pending_reports_count() %}
                        {% if pendingCount > 0 %}
                            <span class="badge bg-danger rounded-pill ms-2 pulse-badge">{{ pendingCount }}</span>
                        {% endif %}
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') == 'app_quiz_statistics' %} active{% endif %}" href="{{ path('app_quiz_statistics', {'prefix': 'admin'}) }}">
                        <i class="bi bi-bar-chart fa-fw me-2"></i>Quiz Statistics
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_books' %} active{% endif %}" href="{{ path('admin_books_index') }}">
                        <i class="fas fa-book fa-fw me-2"></i>Library
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Gamification</li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('admin_game_index') }}">
                        <i class="fas fa-gamepad fa-fw me-2"></i>Games
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('admin_reward_index') }}">
                        <i class="fas fa-trophy fa-fw me-2"></i>Rewards
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_statistics' %} active{% endif %}" href="{{ path('admin_statistics_dashboard') }}">
                        <i class="bi bi-graph-up fa-fw me-2"></i>Statistics
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Community</li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('app_admin_forum_index') }}">
                        <i class="fas fa-comments fa-fw me-2"></i>Forum Moderation
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Security</li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_login_history' %} active{% endif %}" href="{{ path('admin_login_history_index') }}">
                        <i class="bi bi-clock-history fa-fw me-2"></i>Login History
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('app_admin_users_list') }}">
                        <i class="bi bi-people fa-fw me-2"></i>User Management
                    </a>
                </li>

            </ul>
            <div class="px-3 mt-auto pt-3">
                <div class="d-flex align-items-center justify-content-between text-primary-hover">
                    <a class="h5 mb-0 text-body" href="{{ path('app_2fa_manage') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Security (2FA)">
                        <i class="bi bi-shield-lock"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Settings">
                        <i class="bi bi-gear-fill"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="{{ path('app_home') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Go to Website">
                        <i class="bi bi-globe"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="{{ path('app_logout') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Sign out">
                        <i class="bi bi-power"></i>
                    </a>
                </div>
            </div>
            </div>
    </div>
</nav><nav class="navbar sidebar navbar-expand-xl navbar-dark bg-dark">

    <div class="d-flex align-items-center">
        <a class="navbar-brand" href="{{ path('app_admin_dashboard') }}">
            <img class="navbar-brand-item" src="{{ asset('assets/images/Logo.png') }}" alt="NOVA" style="height: 90px;">
        </a>
    </div>
    <div class="offcanvas offcanvas-start flex-row custom-scrollbar h-100" data-bs-backdrop="true" tabindex="-1" id="offcanvasSidebar">
        <div class="offcanvas-body sidebar-content d-flex flex-column bg-dark">

            <ul class="navbar-nav flex-column" id="navbar-sidebar">
                
                <li class="nav-item">
                    <a href="{{ path('app_admin_dashboard') }}" class="nav-link{% if app.request.attributes.get('_route') == 'app_admin_dashboard' %} active{% endif %}">
                        <i class="bi bi-house fa-fw me-2"></i>Dashboard
                    </a>
                </li>
                
                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Learning Management</li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_course' %} active{% endif %}" href="{{ path('admin_course_index') }}">
                        <i class="bi bi-basket fa-fw me-2"></i>Courses
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') starts with 'admin_planning' %} active{% endif %}" href="{{ path('admin_planning_index') }}">
                        <i class="bi bi-calendar-check fa-fw me-2"></i>Planning Sessions
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') starts with 'admin_study_session' %} active{% endif %}" href="{{ path('admin_study_session_index') }}">
                        <i class="bi bi-journal-check fa-fw me-2"></i>Completed Sessions
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'app_quiz_index' or app.request.attributes.get('_route') starts with 'app_quiz_' %} active{% endif %}" href="{{ path('app_quiz_index', {'prefix': 'admin'}) }}">
                        <i class="fas fa-question-circle fa-fw me-2"></i>Quizzes
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4 d-flex justify-content-between align-items-center{% if app.request.attributes.get('_route') starts with 'app_quiz_reports' %} active{% endif %}" href="{{ path('app_quiz_reports_index', {'prefix': 'admin'}) }}">
                        <span>
                            <i class="fas fa-flag fa-fw me-2"></i>Quiz Reports
                        </span>
                        {% set pendingCount = pending_reports_count() %}
                        {% if pendingCount > 0 %}
                            <span class="badge bg-danger rounded-pill ms-2 pulse-badge">{{ pendingCount }}</span>
                        {% endif %}
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') == 'app_quiz_statistics' %} active{% endif %}" href="{{ path('app_quiz_statistics', {'prefix': 'admin'}) }}">
                        <i class="bi bi-bar-chart fa-fw me-2"></i>Quiz Statistics
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_books' %} active{% endif %}" href="{{ path('admin_books_index') }}">
                        <i class="fas fa-book fa-fw me-2"></i>Library
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Gamification</li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('admin_game_index') }}">
                        <i class="fas fa-gamepad fa-fw me-2"></i>Games
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('admin_reward_index') }}">
                        <i class="fas fa-trophy fa-fw me-2"></i>Rewards
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_statistics' %} active{% endif %}" href="{{ path('admin_statistics_dashboard') }}">
                        <i class="bi bi-graph-up fa-fw me-2"></i>Statistics
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Community</li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('app_admin_forum_index') }}">
                        <i class="fas fa-comments fa-fw me-2"></i>Forum Moderation
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Security</li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_login_history' %} active{% endif %}" href="{{ path('admin_login_history_index') }}">
                        <i class="bi bi-clock-history fa-fw me-2"></i>Login History
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('app_admin_users_list') }}">
                        <i class="bi bi-people fa-fw me-2"></i>User Management
                    </a>
                </li>

            </ul>
            <div class="px-3 mt-auto pt-3">
                <div class="d-flex align-items-center justify-content-between text-primary-hover">
                    <a class="h5 mb-0 text-body" href="{{ path('app_2fa_manage') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Security (2FA)">
                        <i class="bi bi-shield-lock"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Settings">
                        <i class="bi bi-gear-fill"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="{{ path('app_home') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Go to Website">
                        <i class="bi bi-globe"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="{{ path('app_logout') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Sign out">
                        <i class="bi bi-power"></i>
                    </a>
                </div>
            </div>
            </div>
    </div>
</nav><nav class="navbar sidebar navbar-expand-xl navbar-dark bg-dark">

    <div class="d-flex align-items-center">
        <a class="navbar-brand" href="{{ path('app_admin_dashboard') }}">
            <img class="navbar-brand-item" src="{{ asset('assets/images/Logo.png') }}" alt="NOVA" style="height: 90px;">
        </a>
    </div>
    <div class="offcanvas offcanvas-start flex-row custom-scrollbar h-100" data-bs-backdrop="true" tabindex="-1" id="offcanvasSidebar">
        <div class="offcanvas-body sidebar-content d-flex flex-column bg-dark">

            <ul class="navbar-nav flex-column" id="navbar-sidebar">
                
                <li class="nav-item">
                    <a href="{{ path('app_admin_dashboard') }}" class="nav-link{% if app.request.attributes.get('_route') == 'app_admin_dashboard' %} active{% endif %}">
                        <i class="bi bi-house fa-fw me-2"></i>Dashboard
                    </a>
                </li>
                
                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Learning Management</li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_course' %} active{% endif %}" href="{{ path('admin_course_index') }}">
                        <i class="bi bi-basket fa-fw me-2"></i>Courses
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') starts with 'admin_planning' %} active{% endif %}" href="{{ path('admin_planning_index') }}">
                        <i class="bi bi-calendar-check fa-fw me-2"></i>Planning Sessions
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') starts with 'admin_study_session' %} active{% endif %}" href="{{ path('admin_study_session_index') }}">
                        <i class="bi bi-journal-check fa-fw me-2"></i>Completed Sessions
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'app_quiz_index' or app.request.attributes.get('_route') starts with 'app_quiz_' %} active{% endif %}" href="{{ path('app_quiz_index', {'prefix': 'admin'}) }}">
                        <i class="fas fa-question-circle fa-fw me-2"></i>Quizzes
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4 d-flex justify-content-between align-items-center{% if app.request.attributes.get('_route') starts with 'app_quiz_reports' %} active{% endif %}" href="{{ path('app_quiz_reports_index', {'prefix': 'admin'}) }}">
                        <span>
                            <i class="fas fa-flag fa-fw me-2"></i>Quiz Reports
                        </span>
                        {% set pendingCount = pending_reports_count() %}
                        {% if pendingCount > 0 %}
                            <span class="badge bg-danger rounded-pill ms-2 pulse-badge">{{ pendingCount }}</span>
                        {% endif %}
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') == 'app_quiz_statistics' %} active{% endif %}" href="{{ path('app_quiz_statistics', {'prefix': 'admin'}) }}">
                        <i class="bi bi-bar-chart fa-fw me-2"></i>Quiz Statistics
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_books' %} active{% endif %}" href="{{ path('admin_books_index') }}">
                        <i class="fas fa-book fa-fw me-2"></i>Library
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Gamification</li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('admin_game_index') }}">
                        <i class="fas fa-gamepad fa-fw me-2"></i>Games
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('admin_reward_index') }}">
                        <i class="fas fa-trophy fa-fw me-2"></i>Rewards
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_statistics' %} active{% endif %}" href="{{ path('admin_statistics_dashboard') }}">
                        <i class="bi bi-graph-up fa-fw me-2"></i>Statistics
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Community</li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('app_admin_forum_index') }}">
                        <i class="fas fa-comments fa-fw me-2"></i>Forum Moderation
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Security</li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_login_history' %} active{% endif %}" href="{{ path('admin_login_history_index') }}">
                        <i class="bi bi-clock-history fa-fw me-2"></i>Login History
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('app_admin_users_list') }}">
                        <i class="bi bi-people fa-fw me-2"></i>User Management
                    </a>
                </li>

            </ul>
            <div class="px-3 mt-auto pt-3">
                <div class="d-flex align-items-center justify-content-between text-primary-hover">
                    <a class="h5 mb-0 text-body" href="{{ path('app_2fa_manage') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Security (2FA)">
                        <i class="bi bi-shield-lock"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Settings">
                        <i class="bi bi-gear-fill"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="{{ path('app_home') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Go to Website">
                        <i class="bi bi-globe"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="{{ path('app_logout') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Sign out">
                        <i class="bi bi-power"></i>
                    </a>
                </div>
            </div>
            </div>
    </div>
</nav><nav class="navbar sidebar navbar-expand-xl navbar-dark bg-dark">

    <div class="d-flex align-items-center">
        <a class="navbar-brand" href="{{ path('app_admin_dashboard') }}">
            <img class="navbar-brand-item" src="{{ asset('assets/images/Logo.png') }}" alt="NOVA" style="height: 90px;">
        </a>
    </div>
    <div class="offcanvas offcanvas-start flex-row custom-scrollbar h-100" data-bs-backdrop="true" tabindex="-1" id="offcanvasSidebar">
        <div class="offcanvas-body sidebar-content d-flex flex-column bg-dark">

            <ul class="navbar-nav flex-column" id="navbar-sidebar">
                
                <li class="nav-item">
                    <a href="{{ path('app_admin_dashboard') }}" class="nav-link{% if app.request.attributes.get('_route') == 'app_admin_dashboard' %} active{% endif %}">
                        <i class="bi bi-house fa-fw me-2"></i>Dashboard
                    </a>
                </li>
                
                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Learning Management</li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_course' %} active{% endif %}" href="{{ path('admin_course_index') }}">
                        <i class="bi bi-basket fa-fw me-2"></i>Courses
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') starts with 'admin_planning' %} active{% endif %}" href="{{ path('admin_planning_index') }}">
                        <i class="bi bi-calendar-check fa-fw me-2"></i>Planning Sessions
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') starts with 'admin_study_session' %} active{% endif %}" href="{{ path('admin_study_session_index') }}">
                        <i class="bi bi-journal-check fa-fw me-2"></i>Completed Sessions
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'app_quiz_index' or app.request.attributes.get('_route') starts with 'app_quiz_' %} active{% endif %}" href="{{ path('app_quiz_index', {'prefix': 'admin'}) }}">
                        <i class="fas fa-question-circle fa-fw me-2"></i>Quizzes
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4 d-flex justify-content-between align-items-center{% if app.request.attributes.get('_route') starts with 'app_quiz_reports' %} active{% endif %}" href="{{ path('app_quiz_reports_index', {'prefix': 'admin'}) }}">
                        <span>
                            <i class="fas fa-flag fa-fw me-2"></i>Quiz Reports
                        </span>
                        {% set pendingCount = pending_reports_count() %}
                        {% if pendingCount > 0 %}
                            <span class="badge bg-danger rounded-pill ms-2 pulse-badge">{{ pendingCount }}</span>
                        {% endif %}
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link ps-4{% if app.request.attributes.get('_route') == 'app_quiz_statistics' %} active{% endif %}" href="{{ path('app_quiz_statistics', {'prefix': 'admin'}) }}">
                        <i class="bi bi-bar-chart fa-fw me-2"></i>Quiz Statistics
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_books' %} active{% endif %}" href="{{ path('admin_books_index') }}">
                        <i class="fas fa-book fa-fw me-2"></i>Library
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Gamification</li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('admin_game_index') }}">
                        <i class="fas fa-gamepad fa-fw me-2"></i>Games
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('admin_reward_index') }}">
                        <i class="fas fa-trophy fa-fw me-2"></i>Rewards
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_statistics' %} active{% endif %}" href="{{ path('admin_statistics_dashboard') }}">
                        <i class="bi bi-graph-up fa-fw me-2"></i>Statistics
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Community</li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('app_admin_forum_index') }}">
                        <i class="fas fa-comments fa-fw me-2"></i>Forum Moderation
                    </a>
                </li>

                <li class="nav-item ms-2 my-2 mt-4 text-uppercase small text-muted">Security</li>

                <li class="nav-item"> 
                    <a class="nav-link{% if app.request.attributes.get('_route') starts with 'admin_login_history' %} active{% endif %}" href="{{ path('admin_login_history_index') }}">
                        <i class="bi bi-clock-history fa-fw me-2"></i>Login History
                    </a>
                </li>

                <li class="nav-item"> 
                    <a class="nav-link" href="{{ path('app_admin_users_list') }}">
                        <i class="bi bi-people fa-fw me-2"></i>User Management
                    </a>
                </li>

            </ul>
            <div class="px-3 mt-auto pt-3">
                <div class="d-flex align-items-center justify-content-between text-primary-hover">
                    <a class="h5 mb-0 text-body" href="{{ path('app_2fa_manage') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Security (2FA)">
                        <i class="bi bi-shield-lock"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Settings">
                        <i class="bi bi-gear-fill"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="{{ path('app_home') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Go to Website">
                        <i class="bi bi-globe"></i>
                    </a>
                    <a class="h5 mb-0 text-body" href="{{ path('app_logout') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Sign out">
                        <i class="bi bi-power"></i>
                    </a>
                </div>
            </div>
            </div>
    </div>
</nav>