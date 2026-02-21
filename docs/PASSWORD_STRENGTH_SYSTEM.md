# Password Strength Meter & Policy Enforcement

## Overview

The NOVA platform includes a comprehensive password strength meter and policy enforcement system that helps users create secure passwords while providing real-time visual feedback.

## Features

### Password Policy Enforcement
- ✅ Minimum 8 characters, maximum 128 characters
- ✅ Requires uppercase letters (A-Z)
- ✅ Requires lowercase letters (a-z)
- ✅ Requires numbers (0-9)
- ✅ Requires special characters (!@#$%^&*...)
- ✅ Minimum strength score requirement
- ✅ Common password detection
- ✅ Pattern detection (repeated characters, etc.)

### Real-time Strength Meter
- 🎨 Visual progress bar with color coding
- 📊 Strength labels (Very Weak to Very Strong)
- ✅ Live requirement checking
- 💡 Helpful feedback and suggestions
- 👁️ Password visibility toggle
- 🔑 Strong password generator

### Server-side Validation
- 🛡️ Custom Symfony validator
- 🔒 Policy service for consistent validation
- ⚠️ Detailed error messages
- 🚫 Prevents weak passwords

## Components

### 1. PasswordPolicyService

**Location**: `src/Service/PasswordPolicyService.php`

Handles all password validation logic.

**Methods**:
```php
// Validate password against policy
validatePassword(string $password): array

// Check password criteria
checkPassword(string $password): array

// Calculate strength (0-5 scale)
calculateStrength(string $password): array

// Get policy requirements
getPolicyRequirements(): array

// Generate strong password
generateStrongPassword(int $length = 16): string
```

**Return Format**:
```php
[
    'valid' => true/false,
    'errors' => ['Error message 1', 'Error message 2'],
    'checks' => [
        'length' => true/false,
        'uppercase' => true/false,
        'lowercase' => true/false,
        'numbers' => true/false,
        'special' => true/false,
        'strength' => [
            'score' => 0-5,
            'percentage' => 0-100,
            'label' => 'Very Weak|Weak|Fair|Strong|Very Strong',
            'color' => 'danger|warning|info|primary|success',
            'feedback' => ['Suggestion 1', 'Suggestion 2']
        ]
    ]
]
```

### 2. Custom Validator

**StrongPassword** (`src/Validator/StrongPassword.php`)
- Symfony constraint for password validation

**StrongPasswordValidator** (`src/Validator/StrongPasswordValidator.php`)
- Validator implementation using PasswordPolicyService

### 3. Password Strength Meter Component

**Location**: `templates/components/password_strength_meter.html.twig`

Reusable Twig component with:
- Password input with visibility toggle
- Real-time strength meter
- Requirement checklist
- Optional password generator

## Usage

### In Forms (Twig Templates)

**Basic Usage**:
```twig
{% include 'components/password_strength_meter.html.twig' %}
```

**With Options**:
```twig
{% include 'components/password_strength_meter.html.twig' with {
    'inputId': 'password',
    'showRequirements': true,
    'showGenerator': true
} %}
```

**Parameters**:
- `inputId` - ID for the password input (default: 'password')
- `showRequirements` - Show requirement checklist (default: true)
- `showGenerator` - Show password generator button (default: false)

### In Controllers (Server-side Validation)

**Using the Service**:
```php
use App\Service\PasswordPolicyService;

public function signup(
    Request $request,
    PasswordPolicyService $passwordPolicy
): Response {
    $password = $request->request->get('password');
    
    $result = $passwordPolicy->validatePassword($password);
    
    if (!$result['valid']) {
        foreach ($result['errors'] as $error) {
            $this->addFlash('error', $error);
        }
        return $this->redirectToRoute('signup');
    }
    
    // Password is valid, proceed...
}
```

**Using the Validator (Entity)**:
```php
use App\Validator\StrongPassword;

class User
{
    #[Assert\NotBlank]
    #[StrongPassword]
    private ?string $password = null;
}
```

## Password Strength Scoring

### Score Calculation (0-5 scale)

**Length Points**:
- 8+ characters: +1 point
- 12+ characters: +1 point
- 16+ characters: +1 point

**Character Variety**:
- Lowercase letters: +1 point
- Uppercase letters: +1 point
- Numbers: +1 point
- Special characters: +1 point

**Penalties**:
- Repeated characters (aaa, 111): -1 point
- Only numbers: -2 points
- Only letters: -1 point
- Common password: -2 points

### Strength Labels

| Score | Label | Color | Description |
|-------|-------|-------|-------------|
| 0-1 | Very Weak | Red | Unacceptable |
| 2 | Weak | Yellow | Not recommended |
| 3 | Fair | Blue | Acceptable |
| 4 | Strong | Primary | Good |
| 5 | Very Strong | Green | Excellent |

## Visual Feedback

### Progress Bar Colors

- 🔴 **Red (0-20%)**: Very Weak - Immediate action needed
- 🟡 **Yellow (21-40%)**: Weak - Needs improvement
- 🔵 **Blue (41-60%)**: Fair - Acceptable
- 🟣 **Primary (61-80%)**: Strong - Good choice
- 🟢 **Green (81-100%)**: Very Strong - Excellent!

### Requirement Checklist

Each requirement shows:
- ⚪ Gray circle: Not met
- ✅ Green checkmark: Met

## Integration Examples

### Signup Form

```twig
<form method="post">
    <div class="mb-3">
        <label for="username">Username</label>
        <input type="text" name="username" class="form-control">
    </div>
    
    <div class="mb-3">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control">
    </div>
    
    {# Password with strength meter #}
    {% include 'components/password_strength_meter.html.twig' with {
        'inputId': 'password',
        'showRequirements': true,
        'showGenerator': true
    } %}
    
    {# Confirm Password #}
    <div class="mb-3">
        <label for="confirmPassword">Confirm Password</label>
        <input type="password" name="confirmPassword" class="form-control">
    </div>
    
    <button type="submit" class="btn btn-primary">Sign Up</button>
</form>
```

### Password Reset Form

```twig
<form method="post">
    <h3>Reset Your Password</h3>
    
    {% include 'components/password_strength_meter.html.twig' with {
        'inputId': 'newPassword',
        'showRequirements': true,
        'showGenerator': false
    } %}
    
    <div class="mb-3">
        <label for="confirmPassword">Confirm New Password</label>
        <input type="password" name="confirmPassword" class="form-control">
    </div>
    
    <button type="submit" class="btn btn-primary">Reset Password</button>
</form>
```

### Change Password Form

```twig
<form method="post">
    <div class="mb-3">
        <label for="currentPassword">Current Password</label>
        <input type="password" name="currentPassword" class="form-control">
    </div>
    
    {% include 'components/password_strength_meter.html.twig' with {
        'inputId': 'newPassword',
        'showRequirements': true,
        'showGenerator': true
    } %}
    
    <div class="mb-3">
        <label for="confirmPassword">Confirm New Password</label>
        <input type="password" name="confirmPassword" class="form-control">
    </div>
    
    <button type="submit" class="btn btn-primary">Change Password</button>
</form>
```

## Configuration

### Customizing Password Policy

Edit `src/Service/PasswordPolicyService.php`:

```php
// Password policy configuration
private const MIN_LENGTH = 8;              // Minimum length
private const MAX_LENGTH = 128;            // Maximum length
private const REQUIRE_UPPERCASE = true;    // Require A-Z
private const REQUIRE_LOWERCASE = true;    // Require a-z
private const REQUIRE_NUMBERS = true;      // Require 0-9
private const REQUIRE_SPECIAL_CHARS = true; // Require !@#$...
private const MIN_STRENGTH_SCORE = 3;      // Minimum score (0-5)
```

### Adding Common Passwords

Add to the `isCommonPassword()` method:

```php
private function isCommonPassword(string $password): bool
{
    $commonPasswords = [
        'password', 'password123', '123456',
        // Add more common passwords here
        'yourcompanyname', 'welcome2024',
    ];
    
    return in_array(strtolower($password), $commonPasswords);
}
```

## JavaScript API

### Functions Available

```javascript
// Check password strength
checkPasswordStrength(inputId)

// Calculate strength
calculatePasswordStrength(password)

// Update requirement checks
updateRequirementChecks(inputId, checks)

// Toggle password visibility
togglePasswordVisibility(inputId)

// Generate strong password
generatePassword(inputId)
```

### Custom Implementation

```javascript
// Get password strength programmatically
const password = document.getElementById('password').value;
const result = calculatePasswordStrength(password);

console.log(result.score);      // 0-5
console.log(result.label);      // 'Strong'
console.log(result.color);      // 'primary'
console.log(result.feedback);   // ['Add symbols']
```

## Security Best Practices

### What We Do

1. ✅ **Client-side validation** - Immediate feedback
2. ✅ **Server-side validation** - Security enforcement
3. ✅ **Common password detection** - Prevent weak passwords
4. ✅ **Pattern detection** - Identify weak patterns
5. ✅ **Strength scoring** - Quantify password quality
6. ✅ **Password generator** - Help users create strong passwords

### What You Should Do

1. ✅ **Always hash passwords** - Use Symfony's password hasher
2. ✅ **Never store plain text** - Only store hashed passwords
3. ✅ **Use HTTPS** - Encrypt password transmission
4. ✅ **Implement rate limiting** - Prevent brute force attacks
5. ✅ **Add 2FA** - Extra security layer (already implemented!)
6. ✅ **Password expiry** - Optional: Force periodic changes

## Troubleshooting

### Strength Meter Not Updating

**Problem**: Meter doesn't respond to typing

**Solutions**:
1. Check JavaScript console for errors
2. Verify `onkeyup` attribute is present
3. Ensure Bootstrap Icons are loaded
4. Clear browser cache

### Validation Not Working

**Problem**: Weak passwords are accepted

**Solutions**:
1. Check PasswordPolicyService is injected
2. Verify validator is registered
3. Clear Symfony cache
4. Check entity has `#[StrongPassword]` attribute

### Generator Not Working

**Problem**: Generate button doesn't work

**Solutions**:
1. Check `showGenerator` is set to `true`
2. Verify JavaScript is loaded
3. Check browser console for errors
4. Ensure clipboard API is available (HTTPS required)

## Testing

### Manual Testing

1. **Test Weak Password**:
   - Enter: "password"
   - Should show: Very Weak (red)
   - Should list: Multiple requirements not met

2. **Test Medium Password**:
   - Enter: "Password123"
   - Should show: Fair (blue)
   - Should list: Missing special characters

3. **Test Strong Password**:
   - Enter: "P@ssw0rd!2024"
   - Should show: Strong (primary/green)
   - Should list: All requirements met

4. **Test Generator**:
   - Click "Generate Strong Password"
   - Should: Create 16-character password
   - Should: Show as Very Strong
   - Should: Copy to clipboard

### Automated Testing

```php
// Test password validation
public function testPasswordValidation()
{
    $service = static::getContainer()->get(PasswordPolicyService::class);
    
    // Test weak password
    $result = $service->validatePassword('weak');
    $this->assertFalse($result['valid']);
    
    // Test strong password
    $result = $service->validatePassword('Str0ng!P@ssw0rd');
    $this->assertTrue($result['valid']);
}
```

## Future Enhancements

Potential improvements:

1. **Password History** - Prevent reusing old passwords
2. **Breach Detection** - Check against known breached passwords
3. **Custom Dictionary** - Company-specific forbidden words
4. **Localization** - Multi-language support
5. **Password Expiry** - Force periodic password changes
6. **Complexity Tiers** - Different requirements for different user roles
7. **Password Hints** - Contextual suggestions
8. **Strength Analytics** - Track password strength across users

## Support

For issues or questions:
- Check this documentation
- Review `src/Service/PasswordPolicyService.php`
- Test with different passwords
- Check browser console for JavaScript errors

---

**Status**: ✅ COMPLETE - Password strength meter and policy enforcement fully implemented!
