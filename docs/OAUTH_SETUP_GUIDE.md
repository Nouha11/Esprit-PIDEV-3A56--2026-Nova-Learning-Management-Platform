# OAuth Authentication Setup Guide (Google & LinkedIn)

This guide will help you set up Google and LinkedIn OAuth authentication for your NOVA platform.

## Overview

Users can now:
- Login with Google
- Login with LinkedIn
- Register with Google
- Register with LinkedIn

All OAuth users are automatically verified and created as students by default.

---

## 1. Google OAuth Setup

### Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Google+ API:
   - Go to "APIs & Services" > "Library"
   - Search for "Google+ API"
   - Click "Enable"

### Step 2: Create OAuth 2.0 Credentials

1. Go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth client ID"
3. Select "Web application"
4. Configure:
   - **Name**: NOVA Platform
   - **Authorized JavaScript origins**: 
     - `http://localhost:8001`
     - `http://127.0.0.1:8001`
     - Add your production URL when deploying
   - **Authorized redirect URIs**:
     - `http://localhost:8001/connect/google/check`
     - `http://127.0.0.1:8001/connect/google/check`
     - Add your production URL when deploying
5. Click "Create"
6. Copy the **Client ID** and **Client Secret**

### Step 3: Add Credentials to .env

Open your `.env` file and update:

```env
GOOGLE_CLIENT_ID=your_google_client_id_here
GOOGLE_CLIENT_SECRET=your_google_client_secret_here
```

---

## 2. LinkedIn OAuth Setup

### Step 1: Create LinkedIn App

1. Go to [LinkedIn Developers](https://www.linkedin.com/developers/apps)
2. Click "Create app"
3. Fill in the required information:
   - **App name**: NOVA Platform
   - **LinkedIn Page**: Select or create a LinkedIn page
   - **Privacy policy URL**: Your privacy policy URL
   - **App logo**: Upload your logo
4. Click "Create app"

### Step 2: Configure OAuth Settings

1. Go to the "Auth" tab
2. Add **Authorized redirect URLs**:
   - `http://localhost:8001/connect/linkedin/check`
   - `http://127.0.0.1:8001/connect/linkedin/check`
   - Add your production URL when deploying
3. Under "OAuth 2.0 scopes", request:
   - `openid`
   - `profile`
   - `email`
4. Copy the **Client ID** and **Client Secret** from the "Application credentials" section

### Step 3: Add Credentials to .env

Open your `.env` file and update:

```env
LINKEDIN_CLIENT_ID=your_linkedin_client_id_here
LINKEDIN_CLIENT_SECRET=your_linkedin_client_secret_here
```

---

## 3. Testing OAuth Authentication

### Test Google Login

1. Start your Symfony server: `symfony server:start` or `php -S localhost:8001 -t public`
2. Go to: `http://localhost:8001/login`
3. Click "Login with Google"
4. You should be redirected to Google's login page
5. After successful authentication, you'll be redirected back and logged in

### Test LinkedIn Login

1. Go to: `http://localhost:8001/login`
2. Click "Login with LinkedIn"
3. You should be redirected to LinkedIn's login page
4. After successful authentication, you'll be redirected back and logged in

### Test Registration

1. Go to: `http://localhost:8001/signup`
2. Click "Sign up with Google" or "Sign up with LinkedIn"
3. A new account will be created automatically with your OAuth profile information

---

## 4. How It Works

### User Flow

1. **New User (Registration)**:
   - User clicks "Sign up with Google/LinkedIn"
   - OAuth provider authenticates the user
   - System checks if email exists in database
   - If not, creates new User with:
     - Email from OAuth
     - Auto-generated username
     - Random password (not used)
     - ROLE_STUDENT role
     - Auto-verified (isVerified = true)
     - Student profile with first/last name from OAuth
   - User is automatically logged in

2. **Existing User (Login)**:
   - User clicks "Login with Google/LinkedIn"
   - OAuth provider authenticates the user
   - System finds existing user by email
   - User is automatically logged in

### Security Features

- OAuth users are auto-verified (no email verification needed)
- Random passwords are generated (users can't login with password)
- Unique usernames are generated from email
- All OAuth traffic is handled securely through Symfony's security system

---

## 5. Bilingual Support

All OAuth messages are translated:
- English: "Account created successfully via Google!"
- French: "Compte créé avec succès via Google !"

The system detects the user's selected language from the session.

---

## 6. Routes

The following routes are available:

- `/connect/google` - Start Google OAuth flow
- `/connect/google/check` - Google OAuth callback
- `/connect/linkedin` - Start LinkedIn OAuth flow
- `/connect/linkedin/check` - LinkedIn OAuth callback

---

## 7. Troubleshooting

### "redirect_uri_mismatch" Error

**Problem**: The redirect URI doesn't match what's configured in Google/LinkedIn.

**Solution**: 
- Make sure the redirect URI in your OAuth provider settings exactly matches your application URL
- Check for trailing slashes
- Ensure you're using the correct protocol (http vs https)

### "invalid_client" Error

**Problem**: Client ID or Client Secret is incorrect.

**Solution**:
- Double-check your `.env` file
- Make sure there are no extra spaces
- Regenerate credentials if needed

### User Not Logged In After OAuth

**Problem**: User is redirected but not authenticated.

**Solution**:
- Clear Symfony cache: `php bin/console cache:clear`
- Check browser console for errors
- Verify security configuration in `config/packages/security.yaml`

### LinkedIn Email Not Available

**Problem**: LinkedIn doesn't return email address.

**Solution**:
- Make sure you've requested the `email` scope in LinkedIn app settings
- Verify your LinkedIn app is approved for email access
- Some LinkedIn accounts may not have email addresses available

---

## 8. Production Deployment

When deploying to production:

1. Update OAuth redirect URIs in Google Cloud Console and LinkedIn Developer Portal
2. Add production URLs to `.env.prod`:
   ```env
   GOOGLE_CLIENT_ID=your_production_google_client_id
   GOOGLE_CLIENT_SECRET=your_production_google_client_secret
   LINKEDIN_CLIENT_ID=your_production_linkedin_client_id
   LINKEDIN_CLIENT_SECRET=your_production_linkedin_client_secret
   ```
3. Use HTTPS for all OAuth redirect URIs
4. Test thoroughly before going live

---

## 9. Files Modified/Created

### Created Files:
- `src/Controller/OAuthController.php` - Handles OAuth authentication
- `src/Security/OAuthAuthenticator.php` - Custom authenticator (optional)
- `config/packages/knpu_oauth2_client.yaml` - OAuth client configuration
- `docs/OAUTH_SETUP_GUIDE.md` - This guide

### Modified Files:
- `.env` - Added OAuth credentials
- `templates/security/login.html.twig` - Added OAuth buttons
- `templates/security/signup_choice.html.twig` - Added OAuth buttons
- `src/Service/FallbackTranslationService.php` - Added OAuth translations
- `composer.json` - Added OAuth packages

---

## 10. Support

If you encounter any issues:
1. Check the Symfony logs: `var/log/dev.log`
2. Enable debug mode in `.env`: `APP_ENV=dev`
3. Clear cache: `php bin/console cache:clear`
4. Check OAuth provider documentation:
   - [Google OAuth Documentation](https://developers.google.com/identity/protocols/oauth2)
   - [LinkedIn OAuth Documentation](https://docs.microsoft.com/en-us/linkedin/shared/authentication/authentication)

---

## Summary

Your NOVA platform now supports:
✅ Google OAuth login/registration
✅ LinkedIn OAuth login/registration
✅ Bilingual support (English/French)
✅ Auto-verification for OAuth users
✅ Automatic student profile creation
✅ Secure authentication flow

Users can now easily sign up and login using their Google or LinkedIn accounts!
