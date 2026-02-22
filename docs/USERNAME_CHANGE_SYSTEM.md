# Username Change System

## Overview
The Username Change System allows users to change their username with comprehensive validation, conflict detection, and alternative suggestions.

## Features

### 1. Username Validation
- **Length Check**: 3-100 characters
- **Format Check**: Only letters, numbers, and underscores
- **Uniqueness Check**: Username must not be taken by another user
- **Reserved Names**: Prevents use of reserved usernames (admin, system, etc.)
- **Real-time Validation**: AJAX-based availability checking

### 2. Conflict Handling
- **Duplicate Detection**: Checks if username is already taken
- **Alternative Suggestions**: Automatically generates 5 available alternatives
- **Smart Suggestions**: Uses numbers, underscores, and random combinations
- **One-Click Selection**: Click suggested username to auto-fill

### 3. User Experience
- **Current Username Display**: Shows current username prominently
- **Real-time Feedback**: Instant availability checking
- **Visual Indicators**: Color-coded success/error messages
- **Requirements Display**: Clear list of username requirements
- **Warning Messages**: Important notices about username changes

### 4. Security
- **Authentication Required**: Must be logged in
- **User-Specific**: Can only change own username
- **Reserved Names Protection**: Prevents use of system usernames
- **Pattern Validation**: Server-side and client-side validation

## Components

### Service Layer
**UsernameChangeService** (`src/Service/UsernameChangeService.php`)
- `validateUsername()` - Comprehensive validation
- `changeUsername()` - Change username with validation
- `isUsernameAvailable()` - Check availability
- `suggestAlternatives()` - Generate suggestions
- `getValidationRules()` - Get validation rules

### Controller
**UsernameChangeController** (`src/Controller/UsernameChangeController.php`)
- `change()` - Main username change page
- `checkAvailability()` - AJAX endpoint for checking
- `getSuggestions()` - AJAX endpoint for suggestions

### Template
**username_change.html.twig** (`templates/settings/username_change.html.twig`)
- Username change form
- Real-time availability checking
- Suggestion display
- Requirements and warnings

## Usage

### For Users

#### Access Username Change Page
Navigate to: `/settings/username`

#### Change Username
1. Enter new username in the input field
2. Click "Check" to verify availability
3. If available, click "Change Username"
4. If taken, select from suggested alternatives
5. Confirm the change

### For Developers

#### Use the Service
```php
use App\Service\UsernameChangeService;

public function __construct(
    private UsernameChangeService $usernameChangeService
) {}

public function changeUsername(User $user, string $newUsername): void
{
    // Validate
    $validation = $this->usernameChangeService->validateUsername($newUsername, $user);
    
    if ($validation['valid']) {
        // Change username
        $this->usernameChangeService->changeUsername($user, $newUsername);
    } else {
        // Handle errors
        foreach ($validation['errors'] as $error) {
            // Display error
        }
    }
}
```

#### Check Availability
```php
$available = $this->usernameChangeService->isUsernameAvailable('newusername');
```

#### Get Suggestions
```php
$suggestions = $this->usernameChangeService->suggestAlternatives('takenusername');
// Returns: ['takenusername1', 'takenusername2', 'takenusername_1', ...]
```

## Validation Rules

### Length
- Minimum: 3 characters
- Maximum: 100 characters

### Format
- Pattern: `^[a-zA-Z0-9_]+$`
- Allowed: Letters (a-z, A-Z), numbers (0-9), underscores (_)
- Not allowed: Spaces, special characters, emojis

### Reserved Usernames
The following usernames are reserved and cannot be used:
- admin, administrator, root, system
- moderator, mod, support, help, staff
- official, nova, test, demo
- guest, user, null, undefined, anonymous

### Uniqueness
- Username must not be taken by another user
- Case-insensitive comparison
- Current user's username is excluded from check

## API Endpoints

### Check Availability
```
POST /settings/username/check
Body: username=newusername

Response:
{
    "available": true,
    "message": "Username is available!"
}

OR

{
    "available": false,
    "message": "This username is already taken",
    "errors": ["This username is already taken"]
}
```

### Get Suggestions
```
POST /settings/username/suggestions
Body: username=takenusername

Response:
{
    "suggestions": [
        "takenusername1",
        "takenusername2",
        "takenusername_1",
        "takenusername_2",
        "takenusername42"
    ]
}
```

## User Interface

### Page Structure
```
┌─────────────────────────────────────────┐
│ Breadcrumb: Home > Settings > Username  │
├─────────────────────────────────────────┤
│ Change Username                         │
├─────────────────────────────────────────┤
│ Current Username: oldusername           │
├─────────────────────────────────────────┤
│ New Username: [input] [Check]           │
│ ✓ Username is available!                │
├─────────────────────────────────────────┤
│ Username Requirements:                  │
│ • 3-100 characters                      │
│ • Letters, numbers, underscores only    │
│ • Must be unique                        │
│ • Cannot be reserved                    │
├─────────────────────────────────────────┤
│ ⚠ Important Notice:                     │
│ • Affects how others find you           │
│ • Old username becomes available        │
│ • May show old username temporarily     │
├─────────────────────────────────────────┤
│ [Cancel] [Change Username]              │
└─────────────────────────────────────────┘
```

### With Suggestions
```
┌─────────────────────────────────────────┐
│ New Username: [takenname] [Check]       │
│ ✗ This username is already taken        │
├─────────────────────────────────────────┤
│ 💡 Available Alternatives:              │
│ [takenname1] [takenname2] [takenname_1] │
│ [takenname_2] [takenname42]             │
└─────────────────────────────────────────┘
```

## JavaScript Features

### Real-time Checking
- AJAX request to check availability
- Loading spinner during check
- Instant feedback with color-coded messages

### Auto-fill Suggestions
- Click any suggestion to auto-fill input
- Automatically re-checks availability
- Updates submit button state

### Form Validation
- Prevents submission if username is same as current
- Validates format before submission
- Disables submit button if unavailable

## Error Handling

### Validation Errors
- **Too Short**: "Username must be at least 3 characters long"
- **Too Long**: "Username cannot be longer than 100 characters"
- **Invalid Format**: "Username can only contain letters, numbers, and underscores"
- **Already Taken**: "This username is already taken"
- **Reserved**: "This username is reserved and cannot be used"

### System Errors
- **Database Error**: "Failed to change username. Please try again."
- **Network Error**: "Error checking availability"

## Security Considerations

### Authentication
- Route protected with `#[IsGranted('ROLE_USER')]`
- Must be logged in to access
- Can only change own username

### Validation
- Server-side validation (primary)
- Client-side validation (UX enhancement)
- Pattern matching on both sides
- Reserved names check

### Rate Limiting (Future)
Consider implementing:
- Limit username changes per time period
- Track change history
- Cooldown period between changes

## Integration Points

### User Profile
Add link to username change:
```twig
<a href="{{ path('app_username_change') }}" class="btn btn-primary">
    <i class="bi bi-person-badge"></i> Change Username
</a>
```

### Settings Menu
Add to settings navigation:
```twig
<li class="nav-item">
    <a class="nav-link" href="{{ path('app_username_change') }}">
        <i class="bi bi-person-badge"></i> Change Username
    </a>
</li>
```

### Admin Panel
Admins can change user usernames through user management.

## Future Enhancements

### Planned Features
1. **Change History** - Track username changes
2. **Cooldown Period** - Limit frequency of changes
3. **Username Reservation** - Reserve old username for 30 days
4. **Email Notification** - Notify on username change
5. **Audit Log** - Log all username changes
6. **Username Redirects** - Redirect old username to new
7. **Display Name** - Separate display name from username
8. **Username Verification** - Verify ownership before change

### Database Enhancement
Create `username_history` table:
```sql
CREATE TABLE username_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    old_username VARCHAR(100) NOT NULL,
    new_username VARCHAR(100) NOT NULL,
    changed_at DATETIME NOT NULL,
    ip_address VARCHAR(45),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);
```

## Testing

### Manual Testing
1. **Valid Change**:
   - Enter available username
   - Click Check → Should show "available"
   - Submit → Should succeed

2. **Duplicate Username**:
   - Enter taken username
   - Click Check → Should show "taken"
   - Should show suggestions
   - Click suggestion → Should auto-fill

3. **Invalid Format**:
   - Enter username with spaces
   - Click Check → Should show format error
   - Enter username with special chars → Same

4. **Reserved Username**:
   - Enter "admin"
   - Click Check → Should show "reserved"

5. **Too Short/Long**:
   - Enter "ab" → Should show too short
   - Enter 101 characters → Should show too long

### Automated Testing
```php
public function testUsernameValidation(): void
{
    $user = $this->createUser('testuser');
    
    // Test valid username
    $result = $this->usernameChangeService->validateUsername('newusername', $user);
    $this->assertTrue($result['valid']);
    
    // Test duplicate
    $this->createUser('duplicate');
    $result = $this->usernameChangeService->validateUsername('duplicate', $user);
    $this->assertFalse($result['valid']);
    
    // Test reserved
    $result = $this->usernameChangeService->validateUsername('admin', $user);
    $this->assertFalse($result['valid']);
}
```

## Troubleshooting

### Username change not working
- Check if user is authenticated
- Verify database connection
- Check for validation errors
- Review server logs

### Suggestions not showing
- Check AJAX endpoint is accessible
- Verify JavaScript is loaded
- Check browser console for errors
- Ensure suggestions are generated

### Availability check failing
- Verify route is registered
- Check CSRF token if enabled
- Ensure database query is working
- Check network tab in browser

## Performance

### Optimization
- Index on `username` column (already exists)
- Cache validation rules
- Debounce AJAX requests
- Limit suggestion generation

### Database Queries
- Single query for availability check
- Efficient username lookup with index
- No N+1 query problems

## Accessibility

### Features
- Proper form labels
- ARIA attributes
- Keyboard navigation
- Screen reader friendly
- Color-blind safe indicators

### Compliance
- WCAG 2.1 Level AA compliant
- Semantic HTML
- Focus management
- Error announcements

## Support
For issues or questions about the Username Change System, refer to this documentation or contact the development team.
