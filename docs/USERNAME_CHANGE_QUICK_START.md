# Username Change System - Quick Start Guide

## What Was Implemented

A complete username change system with real-time validation, conflict detection, and intelligent suggestions.

## Quick Access

**URL**: `/settings/username`

## Key Features

### 1. Real-time Validation ✅
- Check username availability instantly
- AJAX-based checking without page reload
- Visual feedback (green=available, red=taken)

### 2. Conflict Handling ✅
- Detects duplicate usernames
- Generates 5 alternative suggestions
- One-click to select suggestion

### 3. Comprehensive Validation ✅
- Length: 3-100 characters
- Format: Letters, numbers, underscores only
- Reserved names protection
- Uniqueness check

### 4. User-Friendly Interface ✅
- Shows current username
- Clear requirements list
- Important warnings
- Suggestion buttons

## Testing the System

### Test 1: Valid Username Change
```
1. Navigate to /settings/username
2. Enter a new unique username (e.g., "myNewUsername123")
3. Click "Check" button
4. Should show: "✓ Username is available!"
5. Click "Change Username"
6. Should show success message
```

### Test 2: Duplicate Username
```
1. Navigate to /settings/username
2. Enter an existing username (e.g., "admin" or another user's name)
3. Click "Check" button
4. Should show: "✗ This username is already taken"
5. Should display 5 alternative suggestions
6. Click any suggestion
7. Should auto-fill and re-check
```

### Test 3: Invalid Format
```
1. Navigate to /settings/username
2. Enter username with spaces (e.g., "my username")
3. Click "Check" button
4. Should show: "✗ Username can only contain letters, numbers, and underscores"
```

### Test 4: Reserved Username
```
1. Navigate to /settings/username
2. Enter "admin" or "system"
3. Click "Check" button
4. Should show: "✗ This username is reserved and cannot be used"
```

### Test 5: Too Short/Long
```
1. Enter "ab" (too short)
2. Should show: "✗ Username must be at least 3 characters long"

3. Enter 101 characters (too long)
4. Should show: "✗ Username cannot be longer than 100 characters"
```

## Components Created

### Backend
- ✅ `UsernameChangeService` - Business logic
- ✅ `UsernameChangeController` - HTTP handling
- ✅ 3 routes (change page, check, suggestions)

### Frontend
- ✅ `username_change.html.twig` - Main page
- ✅ Real-time AJAX validation
- ✅ Suggestion system
- ✅ Form validation

### Documentation
- ✅ Complete system documentation
- ✅ This quick start guide

## Validation Rules

### Length
- Minimum: 3 characters
- Maximum: 100 characters

### Format
- Allowed: `a-z`, `A-Z`, `0-9`, `_`
- Not allowed: Spaces, special characters

### Reserved Names
Cannot use: admin, administrator, root, system, moderator, mod, support, help, staff, official, nova, test, demo, guest, user, null, undefined, anonymous

### Uniqueness
- Must not be taken by another user
- Your current username is excluded

## API Endpoints

### Check Availability
```
POST /settings/username/check
Body: username=newusername

Response: {"available": true, "message": "Username is available!"}
```

### Get Suggestions
```
POST /settings/username/suggestions
Body: username=takenusername

Response: {"suggestions": ["takenusername1", "takenusername2", ...]}
```

## Integration

### Add to User Profile
```twig
<a href="{{ path('app_username_change') }}" class="btn btn-primary">
    <i class="bi bi-person-badge"></i> Change Username
</a>
```

### Add to Settings Menu
```twig
<li class="nav-item">
    <a class="nav-link" href="{{ path('app_username_change') }}">
        <i class="bi bi-person-badge"></i> Username
    </a>
</li>
```

### Add to Student/Tutor Dashboard
```twig
<div class="card">
    <div class="card-body">
        <h6>Account Settings</h6>
        <a href="{{ path('app_username_change') }}" class="btn btn-sm btn-outline-primary">
            Change Username
        </a>
    </div>
</div>
```

## How It Works

### 1. User Visits Page
- Shows current username
- Displays input field and requirements
- Shows warnings about username changes

### 2. User Enters New Username
- Types in input field
- Clicks "Check" button
- AJAX request sent to server

### 3. Server Validates
- Checks length (3-100 chars)
- Checks format (alphanumeric + underscore)
- Checks if reserved
- Checks if taken by another user

### 4. Server Responds
- If valid: "Username is available!"
- If invalid: Error message + suggestions

### 5. User Submits
- Clicks "Change Username"
- Server validates again (security)
- Updates database
- Shows success message

## Suggestion Algorithm

When username is taken, system generates alternatives:

1. **With Numbers**: `username1`, `username2`, `username3`, `username4`, `username5`
2. **With Underscores**: `username_1`, `username_2`, `username_3`
3. **Random Numbers**: `username42`, `username73`, `username91`

Returns first 5 available suggestions.

## Error Messages

### Validation Errors
- "Username must be at least 3 characters long"
- "Username cannot be longer than 100 characters"
- "Username can only contain letters, numbers, and underscores"
- "This username is already taken"
- "This username is reserved and cannot be used"

### System Errors
- "Failed to change username. Please try again."
- "Error checking availability"

## Security Features

### Authentication
- Must be logged in (`ROLE_USER` required)
- Can only change own username
- Session-based authentication

### Validation
- Server-side validation (primary)
- Client-side validation (UX)
- Pattern matching
- Reserved names protection

### Database
- Unique constraint on username column
- Indexed for fast lookups
- Transaction safety

## User Experience

### Visual Feedback
- ✓ Green for available
- ✗ Red for errors
- 💡 Yellow for suggestions
- ⚠ Orange for warnings

### Interactive Elements
- Real-time checking
- One-click suggestions
- Loading spinners
- Disabled states

### Responsive Design
- Mobile-friendly
- Touch-optimized
- Accessible
- Dark mode compatible

## Troubleshooting

### "Check" button not working
- Check browser console for errors
- Verify JavaScript is loaded
- Check network tab for AJAX requests
- Clear browser cache

### Suggestions not showing
- Ensure username is actually taken
- Check AJAX endpoint is accessible
- Verify response format
- Check browser console

### Form submission failing
- Check validation errors
- Verify authentication
- Check server logs
- Ensure database is accessible

## Next Steps (Optional)

### Immediate Enhancements
1. **Add to Navigation** - Link from user menu
2. **Add to Profile** - Button on profile page
3. **Add to Settings** - Include in settings menu

### Future Features
1. **Change History** - Track all username changes
2. **Cooldown Period** - Limit change frequency
3. **Email Notification** - Notify on change
4. **Username Reservation** - Reserve old name for 30 days
5. **Display Name** - Separate from username

## Files Created

### Backend
- `src/Service/UsernameChangeService.php`
- `src/Controller/UsernameChangeController.php`

### Frontend
- `templates/settings/username_change.html.twig`

### Documentation
- `docs/USERNAME_CHANGE_SYSTEM.md`
- `USERNAME_CHANGE_QUICK_START.md`

## Cache Status
⏳ Need to clear cache: `php bin/console cache:clear`

## Testing Status
⏳ Ready for testing
- Service created
- Controller created
- Template created
- Routes registered

---

**Status**: ✅ COMPLETE AND READY FOR USE

**Next Action**: 
1. Clear cache: `php bin/console cache:clear`
2. Navigate to `/settings/username`
3. Test username change functionality
