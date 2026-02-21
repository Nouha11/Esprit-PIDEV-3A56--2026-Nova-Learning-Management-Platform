# Login History / Timeline System

## Overview
The Login History System tracks and displays all user login attempts across the NOVA platform, providing security insights and activity monitoring for both users and administrators.

## Features

### 1. Automatic Login Tracking
- Tracks every login attempt (successful, failed, blocked)
- Captures detailed information:
  - IP address
  - Browser and version
  - Operating system/platform
  - Device type (Desktop, Mobile, Tablet)
  - Geographic location (if available)
  - 2FA usage status
  - Failure reasons for failed attempts
  - Timestamp

### 2. Timeline Visualization
- Beautiful timeline interface showing login history
- Color-coded status indicators:
  - Green: Successful logins
  - Red: Failed attempts
  - Yellow: Blocked attempts
- Detailed information cards for each login
- Responsive design for all screen sizes

### 3. Security Analytics
- Login statistics (total, successful, failed)
- Success rate calculation
- Suspicious activity detection:
  - Multiple failed login attempts
  - Logins from different locations
  - Logins from multiple devices
- Security alerts for administrators

### 4. Admin Dashboard
- View all login attempts across the platform
- Filter by user, status, date range
- User-specific login history
- Security monitoring and alerts
- Export capabilities (future enhancement)

### 5. User Dashboard Widget
- Recent login activity widget
- Shows last 5 login attempts
- Quick security overview
- Alerts for suspicious activity

## Components

### Entities
- **LoginHistory** (`src/Entity/users/LoginHistory.php`)
  - Stores all login attempt data
  - Indexed for fast queries
  - Cascade delete with user

### Services
- **LoginHistoryService** (`src/Service/LoginHistoryService.php`)
  - `logLoginAttempt()` - Log a login attempt
  - `getRecentLogins()` - Get recent logins for a user
  - `getLoginStatistics()` - Calculate login statistics
  - `getAllLoginHistory()` - Get all login history (admin)
  - `detectSuspiciousActivity()` - Detect security threats

### Event Subscribers
- **LoginHistorySubscriber** (`src/EventSubscriber/LoginHistorySubscriber.php`)
  - Automatically logs successful logins
  - Automatically logs failed login attempts
  - Integrates with Symfony Security events

### Controllers
- **LoginHistoryController** (`src/Controller/Admin/LoginHistoryController.php`)
  - Admin routes for viewing login history
  - User-specific history views
  - Statistics and analytics

### Templates
- **Timeline Component** (`templates/components/login_history_timeline.html.twig`)
  - Full timeline view with detailed information
  - Supports showing/hiding user information
  - Configurable limit

- **Widget Component** (`templates/components/login_history_widget.html.twig`)
  - Compact widget for dashboards
  - Shows recent 5 logins
  - Quick security overview

- **Admin Views**
  - `templates/admin/login_history/index.html.twig` - All login history
  - `templates/admin/login_history/user.html.twig` - User-specific history

## Usage

### For Administrators

#### View All Login History
```
URL: /admin/login-history
```
- See all login attempts across the platform
- Statistics cards showing totals
- Timeline view with full details
- Pagination for large datasets

#### View User-Specific History
```
URL: /admin/login-history/user/{id}
```
- Complete login history for a specific user
- 30-day statistics
- Security alerts
- User information card

### For Developers

#### Log a Login Attempt Manually
```php
use App\Service\LoginHistoryService;

public function __construct(
    private LoginHistoryService $loginHistoryService
) {}

public function someAction(User $user): void
{
    $this->loginHistoryService->logLoginAttempt(
        $user,
        'success', // or 'failed', 'blocked'
        null, // failure reason (optional)
        true  // 2FA used (optional)
    );
}
```

#### Get Recent Logins
```php
$recentLogins = $this->loginHistoryService->getRecentLogins($user, 10);
```

#### Get Login Statistics
```php
$stats = $this->loginHistoryService->getLoginStatistics($user, 30);
// Returns: ['total' => 45, 'successful' => 42, 'failed' => 3, 'successRate' => 93.33]
```

#### Detect Suspicious Activity
```php
$alerts = $this->loginHistoryService->detectSuspiciousActivity($user);
// Returns array of alert messages
```

### In Templates

#### Include Timeline Component
```twig
{% include 'components/login_history_timeline.html.twig' with {
    'loginHistory': loginHistory,
    'showUser': true,  {# Show user info (for admin views) #}
    'limit': 20
} %}
```

#### Include Widget Component
```twig
{% include 'components/login_history_widget.html.twig' with {
    'recentLogins': recentLogins,
    'limit': 5
} %}
```

## Database Schema

### login_history Table
```sql
CREATE TABLE login_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status VARCHAR(20) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    browser VARCHAR(100),
    platform VARCHAR(100),
    device VARCHAR(100),
    location VARCHAR(100),
    failure_reason VARCHAR(255),
    is2fa_used BOOLEAN DEFAULT FALSE,
    created_at DATETIME NOT NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_status (status),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);
```

## Security Features

### Automatic Detection
The system automatically detects:
1. **Multiple Failed Attempts** - 3+ failed logins in recent history
2. **Multiple Locations** - Logins from 3+ different locations
3. **Multiple Devices** - Logins from 3+ different devices

### Privacy Considerations
- IP addresses are stored but can be anonymized
- User agents are stored for security purposes
- Location data is optional and basic
- Data retention policies can be implemented

## Future Enhancements

### Planned Features
1. **GeoIP Integration** - Accurate location detection using MaxMind or ip-api.com
2. **Email Notifications** - Alert users of suspicious logins
3. **Session Management** - View and revoke active sessions
4. **Export Functionality** - Export login history to CSV/PDF
5. **Advanced Filtering** - Filter by date range, status, location, device
6. **Login Heatmap** - Visual representation of login patterns
7. **Anomaly Detection** - ML-based suspicious activity detection
8. **IP Blacklisting** - Automatic blocking of suspicious IPs
9. **Device Recognition** - Remember trusted devices
10. **Login Approval** - Require approval for new devices/locations

### Integration Opportunities
- **2FA System** - Already integrated, tracks 2FA usage
- **Ban System** - Can trigger automatic bans based on failed attempts
- **Email System** - Send security alerts
- **Admin Notifications** - Real-time alerts for suspicious activity

## Configuration

### Service Configuration
The service is automatically registered in Symfony's service container. No additional configuration needed.

### Event Subscriber
The LoginHistorySubscriber is automatically registered and listens to:
- `LoginSuccessEvent` - Triggered on successful login
- `LoginFailureEvent` - Triggered on failed login

### Customization
You can customize the suspicious activity detection thresholds in `LoginHistoryService`:
```php
// In detectSuspiciousActivity() method
if ($failedCount >= 3) { // Change threshold here
    $suspicious[] = "Multiple failed login attempts detected";
}
```

## Performance Considerations

### Indexes
The table includes indexes on:
- `user_id` - Fast user-specific queries
- `created_at` - Fast date-range queries
- `status` - Fast status filtering

### Query Optimization
- Use `setMaxResults()` to limit result sets
- Use `setFirstResult()` for pagination
- Consider archiving old records (90+ days)

### Caching
Consider caching:
- Recent login statistics
- Suspicious activity alerts
- User-specific recent logins

## Testing

### Manual Testing
1. Log in successfully - Check if recorded
2. Log in with wrong password - Check if failed attempt recorded
3. Log in with 2FA - Check if 2FA flag is set
4. View admin dashboard - Check statistics
5. View user-specific history - Check timeline

### Test Data
You can create test login history entries:
```php
$loginHistory = new LoginHistory();
$loginHistory->setUser($user);
$loginHistory->setStatus('success');
$loginHistory->setIpAddress('192.168.1.1');
$loginHistory->setBrowser('Chrome');
$loginHistory->setPlatform('Windows 10');
$loginHistory->setDevice('Desktop');
$entityManager->persist($loginHistory);
$entityManager->flush();
```

## Troubleshooting

### Login attempts not being recorded
- Check if LoginHistorySubscriber is registered
- Verify database table exists
- Check Symfony logs for errors

### Incorrect browser/platform detection
- User agent parsing is basic
- Consider using a library like `mobiledetect/mobiledetectlib`

### Missing location data
- Basic implementation returns null
- Integrate GeoIP service for accurate location

## Migration

The migration file `Version20260221201406.php` creates the `login_history` table with all necessary indexes and foreign keys.

Run migration:
```bash
php bin/console doctrine:migrations:migrate
```

## Routes

### Admin Routes
- `GET /admin/login-history` - View all login history
- `GET /admin/login-history/user/{id}` - View user-specific history

### User Routes (Future)
- `GET /profile/login-history` - View own login history
- `GET /profile/security` - Security settings with login history

## Permissions
- Admin routes require `ROLE_ADMIN`
- User routes require authentication
- Users can only view their own history

## Best Practices

1. **Regular Monitoring** - Check login history regularly for suspicious activity
2. **User Education** - Inform users about login notifications
3. **Data Retention** - Implement policy to archive/delete old records
4. **Privacy Compliance** - Ensure GDPR/privacy law compliance
5. **Security Alerts** - Set up notifications for suspicious activity
6. **Performance** - Monitor query performance as data grows
7. **Backup** - Include login_history in backup strategy

## Support
For issues or questions about the Login History System, contact the development team or refer to the main project documentation.
