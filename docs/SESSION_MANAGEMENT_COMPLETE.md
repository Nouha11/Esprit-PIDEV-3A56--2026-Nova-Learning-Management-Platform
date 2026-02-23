# Session Management & Device Tracking - Implementation Complete

## Overview
Implemented comprehensive session management system that allows users to see "Where you're logged in" and remotely log out from devices.

## Features Implemented

### 1. Session Tracking
- **Automatic Session Creation**: Sessions are automatically created on login
- **Device Detection**: Identifies browser, platform (Windows/macOS/Linux/iOS/Android), and device type (Desktop/Mobile/Tablet)
- **IP Address Tracking**: Records IP address for each session
- **Location Detection**: Placeholder for GeoIP integration (currently shows "Local" for localhost)
- **Last Activity Tracking**: Updates timestamp on each request

### 2. Session Management Interface
- **Active Sessions Page**: `/sessions` - Shows all active sessions with device details
- **Current Session Indicator**: Highlights the current session with green border and badge
- **Device Icons**: Visual indicators for Desktop, Mobile, and Tablet devices
- **Session Details**: Shows browser, platform, location, last activity, and sign-in time

### 3. Remote Logout Capabilities
- **Individual Session Termination**: Log out from specific devices
- **Bulk Termination**: "Log Out All Other Devices" button (keeps current session active)
- **AJAX-based**: Smooth UI updates without page reload
- **Confirmation Dialogs**: Prevents accidental logouts

### 4. Security Features
- **Session Token**: Unique 64-character hex token for each session
- **Current Session Protection**: Cannot terminate your current session via remote logout
- **User Isolation**: Users can only manage their own sessions
- **Automatic Cleanup**: Old sessions (30+ days inactive) are automatically deactivated

## Files Created

### Controllers
- `src/Controller/SessionManagementController.php` - Handles session viewing and termination

### Services
- `src/Service/SessionManagementService.php` - Core session management logic
  - `createSession()` - Create or update session on login
  - `updateSessionActivity()` - Update last activity timestamp
  - `getActiveSessions()` - Get all active sessions for a user
  - `terminateSession()` - Terminate specific session
  - `terminateAllOtherSessions()` - Terminate all except current
  - `parseUserAgent()` - Parse browser/platform/device from user agent

### Entities
- `src/Entity/users/UserSession.php` - Session entity with device tracking

### Repositories
- `src/Repository/UserSessionRepository.php` - Database queries for sessions

### Event Subscribers
- `src/EventSubscriber/LoginHistorySubscriber.php` - Updated to create sessions on login
- `src/EventSubscriber/SessionActivitySubscriber.php` - Updates session activity on each request

### Commands
- `src/Command/CleanupSessionsCommand.php` - Cleanup old sessions (run via cron)

### Templates
- `templates/security/sessions.html.twig` - Active sessions management page

### Database
- Migration: `migrations/Version20260222145616.php` - Creates `user_sessions` table

## Database Schema

```sql
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    browser VARCHAR(100),
    platform VARCHAR(100),
    device VARCHAR(100),
    location VARCHAR(100),
    created_at DATETIME NOT NULL,
    last_activity DATETIME NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_current BOOLEAN DEFAULT FALSE,
    INDEX idx_user_active (user_id, is_active),
    INDEX idx_session_token (session_token),
    INDEX idx_last_activity (last_activity),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);
```

## Routes

| Route | Method | Description |
|-------|--------|-------------|
| `/sessions` | GET | View all active sessions |
| `/sessions/terminate/{id}` | POST | Terminate specific session |
| `/sessions/terminate-all` | POST | Terminate all other sessions |

## Integration Points

### Dashboard Sidebars
Added "Active Sessions" link to:
- Student Dashboard (`templates/front/users/student/dashboard.html.twig`)
- Tutor Dashboard (`templates/front/users/tutor/dashboard.html.twig`)

### Login Flow
- `LoginHistorySubscriber` now creates/updates sessions on successful login
- Session token stored in Symfony session
- Device information parsed from User-Agent header

### Request Lifecycle
- `SessionActivitySubscriber` updates `last_activity` on each authenticated request
- Runs on every main request for logged-in users
- Fails silently to avoid breaking requests

## User Agent Parsing

Detects:
- **Browsers**: Chrome, Firefox, Safari, Edge, IE
- **Platforms**: Windows (7/8/8.1/10/11), macOS, Linux, Android, iOS
- **Devices**: Desktop, Mobile, Tablet

## Maintenance

### Cleanup Command
```bash
php bin/console app:cleanup-sessions
```

Deactivates sessions inactive for 30+ days. Recommended to run daily via cron:
```bash
0 2 * * * cd /path/to/project && php bin/console app:cleanup-sessions
```

## Security Considerations

1. **Session Token**: 64-character random hex string (256-bit entropy)
2. **User Isolation**: Users can only view/terminate their own sessions
3. **Current Session Protection**: Cannot remotely terminate current session
4. **Cascade Delete**: Sessions deleted when user is deleted
5. **Activity Tracking**: Last activity updated on each request

## Future Enhancements

1. **GeoIP Integration**: Add real location detection using MaxMind or similar
2. **Email Notifications**: Alert users when new device logs in
3. **Suspicious Activity Detection**: Flag unusual login patterns
4. **Session Limits**: Enforce maximum concurrent sessions per user
5. **Device Naming**: Allow users to name their devices
6. **Browser Fingerprinting**: More accurate device identification

## Testing

1. Log in from multiple browsers/devices
2. Visit `/sessions` to see all active sessions
3. Test remote logout from one device
4. Test "Log Out All Other Devices" functionality
5. Verify current session cannot be terminated
6. Check session activity updates on page navigation

## Usage Example

```php
// In a controller
$activeSessions = $this->sessionManagementService->getActiveSessions($user);

// Terminate a session
$success = $this->sessionManagementService->terminateSession($sessionId, $user);

// Terminate all other sessions
$count = $this->sessionManagementService->terminateAllOtherSessions($user);

// Cleanup old sessions
$count = $this->sessionManagementService->cleanupOldSessions();
```

## UI Features

- **Responsive Design**: Works on all screen sizes
- **Device Icons**: Visual indicators for device types
- **Color Coding**: Green for current session, blue for others
- **Real-time Updates**: AJAX-based termination without page reload
- **Success Messages**: Toast notifications for user feedback
- **Confirmation Dialogs**: Prevent accidental logouts
- **Info Card**: Explains what active sessions are

## Accessibility

- Semantic HTML structure
- ARIA labels for icons
- Keyboard navigation support
- Screen reader friendly
- High contrast colors

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

---

**Status**: ✅ Complete and tested
**Date**: February 22, 2026
