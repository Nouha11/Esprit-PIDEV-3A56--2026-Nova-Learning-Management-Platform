# Two-Factor Authentication (2FA) System

## Overview

NOVA Platform now includes a comprehensive Two-Factor Authentication (2FA) system using TOTP (Time-based One-Time Password) to enhance account security. Users can enable 2FA to add an extra layer of protection to their accounts.

## Features

- **TOTP-based Authentication**: Uses industry-standard Time-based One-Time Password algorithm
- **QR Code Generation**: Easy setup with QR code scanning
- **Manual Secret Entry**: Alternative setup method for users who can't scan QR codes
- **Multiple Authenticator App Support**: Compatible with Google Authenticator, Microsoft Authenticator, Authy, 1Password, and more
- **Custom Authentication Form**: Beautiful, branded 2FA verification page
- **User-Friendly Management**: Easy enable/disable interface
- **Security Verification**: Requires current 2FA code to disable the feature

## Installation

The following packages have been installed:

```bash
composer require scheb/2fa-bundle:^7.0 scheb/2fa-totp:^7.0
```

## Database Changes

Two new fields have been added to the `user` table:

- `totp_enabled` (TINYINT): Whether 2FA is enabled for the user
- `totp_secret` (VARCHAR(255)): The secret key for TOTP generation

## Configuration

### Bundle Configuration

File: `config/packages/scheb_2fa.yaml`

```yaml
scheb_two_factor:
    security_tokens:
        - Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken
        - Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken
    
    totp:
        enabled: true
        server_name: 'NOVA Platform'
        issuer: 'NOVA'
        leeway: 1
        template: 'security/2fa/form.html.twig'
```

## User Entity Changes

The `User` entity now implements `TwoFactorInterface` and includes:

```php
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $totpEnabled = false;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $totpSecret = null;

    // Required methods for TwoFactorInterface
    public function isTotpAuthenticationEnabled(): bool
    public function getTotpAuthenticationUsername(): string
    public function getTotpAuthenticationConfiguration(): ?TotpConfigurationInterface
}
```

## Routes

### User Routes

- `/2fa/setup` - Setup 2FA (GET/POST)
- `/2fa/manage` - Manage 2FA settings (GET)
- `/2fa/disable` - Disable 2FA (POST)

### System Routes (Auto-configured)

- `/2fa` - 2FA authentication form (shown after login if 2FA is enabled)
- `/2fa_check` - 2FA verification endpoint

## Service: TwoFactorService

Location: `src/Service/TwoFactorService.php`

### Methods

- `generateSecret()`: Generate a new TOTP secret
- `enableTwoFactor(User $user)`: Initialize 2FA for a user
- `verifyAndEnable(User $user, string $code)`: Verify code and enable 2FA
- `disableTwoFactor(User $user)`: Disable 2FA for a user
- `generateQrCode(User $user)`: Generate QR code data URI
- `verifyCode(User $user, string $code)`: Verify a TOTP code

## Controller: TwoFactorController

Location: `src/Controller/TwoFactorController.php`

Handles all 2FA management operations for authenticated users.

## Templates

### 1. Setup Page (`templates/security/2fa/setup.html.twig`)

Features:
- QR code display for easy scanning
- Manual secret key entry option
- 6-digit code verification form
- Recommended authenticator apps list
- Copy-to-clipboard functionality

### 2. Management Page (`templates/security/2fa/manage.html.twig`)

Features:
- Current 2FA status display
- Enable/Disable options
- Security information
- Confirmation modal for disabling

### 3. Authentication Form (`templates/security/2fa/form.html.twig`)

Features:
- Beautiful branded design matching NOVA theme
- 6-digit code input with auto-formatting
- Trust device option (for future implementation)
- Help and logout links

## User Flow

### Enabling 2FA

1. User navigates to `/2fa/manage`
2. Clicks "Enable Two-Factor Authentication"
3. Redirected to `/2fa/setup`
4. System generates a secret and QR code
5. User scans QR code with authenticator app
6. User enters 6-digit code to verify
7. 2FA is enabled upon successful verification

### Login with 2FA

1. User enters username and password
2. After successful password authentication, redirected to `/2fa`
3. User enters 6-digit code from authenticator app
4. Upon successful verification, user is fully authenticated

### Disabling 2FA

1. User navigates to `/2fa/manage`
2. Clicks "Disable 2FA"
3. Modal appears requesting current 6-digit code
4. User enters code to confirm
5. 2FA is disabled upon successful verification

## Security Considerations

1. **Secret Storage**: TOTP secrets are stored encrypted in the database
2. **Verification Required**: Users must verify with a current code to disable 2FA
3. **Leeway**: 1-period leeway allows for minor time synchronization issues
4. **No Backup Codes**: Consider implementing backup codes for account recovery
5. **Rate Limiting**: Consider adding rate limiting to prevent brute force attacks

## Recommended Authenticator Apps

- **Google Authenticator** (iOS & Android)
- **Microsoft Authenticator** (iOS & Android)
- **Authy** (iOS, Android & Desktop)
- **1Password** (Premium feature)
- **LastPass Authenticator**
- **Duo Mobile**

## Integration with Profile

To add a link to 2FA management in user profiles, add:

```twig
<a href="{{ path('app_2fa_manage') }}" class="btn btn-outline-primary">
    <i class="bi bi-shield-lock me-2"></i>Manage Two-Factor Authentication
</a>
```

## Testing

### Manual Testing Steps

1. **Setup Test**:
   - Login as a user
   - Navigate to `/2fa/manage`
   - Click "Enable 2FA"
   - Scan QR code with authenticator app
   - Enter code and verify it enables successfully

2. **Login Test**:
   - Logout
   - Login with username/password
   - Verify 2FA form appears
   - Enter code from authenticator app
   - Verify successful login

3. **Disable Test**:
   - Navigate to `/2fa/manage`
   - Click "Disable 2FA"
   - Enter current code
   - Verify 2FA is disabled

## Future Enhancements

1. **Backup Codes**: Generate one-time backup codes for account recovery
2. **Trusted Devices**: Remember devices for 30 days
3. **SMS Fallback**: Alternative 2FA method via SMS
4. **Email Fallback**: Alternative 2FA method via email
5. **Admin Override**: Allow admins to disable 2FA for users
6. **2FA Enforcement**: Require 2FA for certain user roles
7. **Activity Log**: Track 2FA enable/disable events
8. **Recovery Options**: Account recovery process if user loses authenticator

## Troubleshooting

### QR Code Not Displaying

- Check that endroid/qr-code package is installed
- Verify QR code generation in TwoFactorService
- Check browser console for errors

### Invalid Code Errors

- Verify device time is synchronized
- Check leeway configuration
- Ensure secret is properly stored in database

### 2FA Not Triggering on Login

- Verify `totpEnabled` is true in database
- Check security configuration includes correct tokens
- Clear Symfony cache

## Support

For issues or questions about the 2FA system, please contact the development team or refer to:

- [Scheb 2FA Bundle Documentation](https://symfony.com/bundles/SchebTwoFactorBundle/current/index.html)
- [TOTP RFC 6238](https://tools.ietf.org/html/rfc6238)
