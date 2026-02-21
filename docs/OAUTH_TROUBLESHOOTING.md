# OAuth Troubleshooting Guide

## Error 401: invalid_client

This error occurs when OAuth credentials are not properly configured or the redirect URI doesn't match.

### Common Causes

1. **Wrong Domain/URL**: The client is accessing from a different domain than configured
2. **Missing Credentials**: OAuth credentials not set in `.env.local`
3. **Incorrect Redirect URI**: The redirect URI in Google/LinkedIn console doesn't match
4. **Environment Mismatch**: Using production credentials in development or vice versa

### Solutions

#### Solution 1: Configure Multiple Redirect URIs (Recommended)

Add all possible redirect URIs in your OAuth provider console:

**For Google OAuth Console:**
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project
3. Go to "APIs & Services" → "Credentials"
4. Click on your OAuth 2.0 Client ID
5. Add these Authorized redirect URIs:
   ```
   http://localhost:8000/connect/google/check
   http://127.0.0.1:8000/connect/google/check
   http://your-local-ip:8000/connect/google/check
   http://your-domain.com/connect/google/check
   https://your-domain.com/connect/google/check
   ```

**For LinkedIn OAuth:**
1. Go to [LinkedIn Developers](https://www.linkedin.com/developers/apps)
2. Select your app
3. Go to "Auth" tab
4. Add these Redirect URLs:
   ```
   http://localhost:8000/connect/linkedin/check
   http://127.0.0.1:8000/connect/linkedin/check
   http://your-local-ip:8000/connect/linkedin/check
   http://your-domain.com/connect/linkedin/check
   https://your-domain.com/connect/linkedin/check
   ```

#### Solution 2: Environment-Specific Credentials

Create different OAuth apps for different environments:

**Development Environment:**
- Create OAuth app with localhost redirect URIs
- Store credentials in `.env.local`

**Production Environment:**
- Create separate OAuth app with production domain
- Store credentials in production `.env.local`

**Example `.env.local` for Development:**
```env
# Google OAuth (Development)
GOOGLE_CLIENT_ID=your-dev-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-dev-client-secret

# LinkedIn OAuth (Development)
LINKEDIN_CLIENT_ID=your-dev-linkedin-id
LINKEDIN_CLIENT_SECRET=your-dev-linkedin-secret
```

**Example `.env.local` for Production:**
```env
# Google OAuth (Production)
GOOGLE_CLIENT_ID=your-prod-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-prod-client-secret

# LinkedIn OAuth (Production)
LINKEDIN_CLIENT_ID=your-prod-linkedin-id
LINKEDIN_CLIENT_SECRET=your-prod-linkedin-secret
```

#### Solution 3: Disable OAuth for Local Testing

If you're testing locally and don't need OAuth, you can temporarily disable it:

1. Comment out OAuth buttons in templates:
```twig
{# Temporarily disabled for local testing
<a href="{{ path('connect_google_start') }}" class="btn bg-google mb-2">
    <i class="fab fa-fw fa-google text-white me-2"></i>Login with Google
</a>
#}
```

2. Use regular username/password login instead

#### Solution 4: Check Current Configuration

Verify your current setup:

1. **Check `.env.local` exists and has credentials:**
```bash
cat .env.local
```

2. **Verify redirect routes are working:**
```bash
php bin/console debug:router | grep connect
```

Should show:
```
connect_google_start     ANY    /connect/google
connect_google_check     ANY    /connect/google/check
connect_linkedin_start   ANY    /connect/linkedin
connect_linkedin_check   ANY    /connect/linkedin/check
```

3. **Test the redirect URL:**
- The URL should be: `http://your-domain:8000/connect/google/check`
- Make sure this EXACT URL is in your OAuth console

### Quick Fix for Testing

If you need a quick fix for testing on another PC:

1. **Find the client's IP/domain:**
```bash
# On the client PC
ipconfig  # Windows
ifconfig  # Linux/Mac
```

2. **Add the redirect URI to OAuth console:**
```
http://CLIENT_IP:8000/connect/google/check
http://CLIENT_IP:8000/connect/linkedin/check
```

3. **Access using that IP:**
```
http://CLIENT_IP:8000/login
```

### Verification Steps

After making changes:

1. **Clear Symfony cache:**
```bash
php bin/console cache:clear
```

2. **Test OAuth flow:**
- Click "Login with Google"
- Should redirect to Google login
- After login, should redirect back to your app
- Should create/login user successfully

3. **Check for errors:**
- Browser console (F12)
- Symfony logs: `var/log/dev.log`
- OAuth provider logs

### Common Mistakes

❌ **Wrong:**
```
Redirect URI: http://localhost:8000/connect/google
```

✅ **Correct:**
```
Redirect URI: http://localhost:8000/connect/google/check
```

❌ **Wrong:**
```
Using production credentials in development
```

✅ **Correct:**
```
Separate credentials for dev and production
```

❌ **Wrong:**
```
Only one redirect URI configured
```

✅ **Correct:**
```
Multiple redirect URIs for different environments
```

### Security Notes

1. **Never commit `.env.local`** - It contains sensitive credentials
2. **Use HTTPS in production** - OAuth providers require HTTPS for production
3. **Regenerate credentials** if they were exposed
4. **Restrict OAuth scopes** to only what you need
5. **Monitor OAuth usage** in provider console

### Testing Checklist

- [ ] OAuth credentials are in `.env.local`
- [ ] Redirect URIs match exactly in OAuth console
- [ ] Routes are accessible (`debug:router`)
- [ ] Cache is cleared
- [ ] Firewall allows OAuth routes
- [ ] HTTPS is used in production
- [ ] Multiple redirect URIs configured for different environments

### Getting Help

If you still have issues:

1. Check Symfony logs: `var/log/dev.log`
2. Check browser console for JavaScript errors
3. Verify OAuth provider status page
4. Test with a different browser/incognito mode
5. Check firewall/antivirus settings

### Production Deployment

When deploying to production:

1. Create production OAuth apps
2. Add production domain to redirect URIs
3. Use HTTPS (required by OAuth providers)
4. Set credentials in production `.env.local`
5. Test OAuth flow thoroughly
6. Monitor for errors

### Additional Resources

- [Google OAuth Documentation](https://developers.google.com/identity/protocols/oauth2)
- [LinkedIn OAuth Documentation](https://docs.microsoft.com/en-us/linkedin/shared/authentication/authentication)
- [KnpU OAuth Client Bundle](https://github.com/knpuniversity/oauth2-client-bundle)
- [Symfony Security Documentation](https://symfony.com/doc/current/security.html)
