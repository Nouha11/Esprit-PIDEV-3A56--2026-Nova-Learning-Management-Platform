# OAuth Quick Fix - Error 401: invalid_client

## Problem
Someone on another PC is getting "Error 401: invalid_client" when trying to use OAuth login.

## Root Cause
The redirect URI doesn't match what's configured in Google/LinkedIn OAuth console.

## Quick Fix (5 minutes)

### Step 1: Find Your Current URL
The client should be accessing your app via a URL like:
- `http://192.168.1.100:8000` (local network IP)
- `http://localhost:8000` (if on same machine)
- `http://your-domain.com` (if deployed)

### Step 2: Add Redirect URIs to Google OAuth

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project
3. Go to **APIs & Services** → **Credentials**
4. Click on your **OAuth 2.0 Client ID**
5. Under **Authorized redirect URIs**, add:
   ```
   http://localhost:8000/connect/google/check
   http://127.0.0.1:8000/connect/google/check
   http://192.168.1.100:8000/connect/google/check
   ```
   (Replace `192.168.1.100` with your actual IP)

6. Click **Save**

### Step 3: Add Redirect URIs to LinkedIn OAuth

1. Go to [LinkedIn Developers](https://www.linkedin.com/developers/apps)
2. Select your app
3. Go to **Auth** tab
4. Under **Redirect URLs**, add:
   ```
   http://localhost:8000/connect/linkedin/check
   http://127.0.0.1:8000/connect/linkedin/check
   http://192.168.1.100:8000/connect/linkedin/check
   ```
   (Replace `192.168.1.100` with your actual IP)

5. Click **Update**

### Step 4: Clear Cache
```bash
php bin/console cache:clear
```

### Step 5: Test
1. Go to login page
2. Click "Login with Google" or "Login with LinkedIn"
3. Should work now!

## Check Your Configuration

Run this command to see your current OAuth setup:
```bash
php bin/console app:check-oauth
```

This will show you:
- Current OAuth credentials status
- Redirect URIs that need to be configured
- Step-by-step instructions

## Common Issues

### Issue 1: Wrong Redirect URI Format
❌ Wrong: `http://localhost:8000/connect/google`
✅ Correct: `http://localhost:8000/connect/google/check`

Note the `/check` at the end!

### Issue 2: Missing .env.local File
Make sure you have a `.env.local` file with:
```env
GOOGLE_CLIENT_ID=your-actual-client-id
GOOGLE_CLIENT_SECRET=your-actual-secret
LINKEDIN_CLIENT_ID=your-actual-client-id
LINKEDIN_CLIENT_SECRET=your-actual-secret
```

### Issue 3: Using Wrong Port
If your app runs on port 8001 instead of 8000, update all redirect URIs:
```
http://localhost:8001/connect/google/check
http://localhost:8001/connect/linkedin/check
```

## For Production Deployment

When deploying to a real domain:

1. **Use HTTPS** (required by OAuth providers)
2. Add production redirect URIs:
   ```
   https://your-domain.com/connect/google/check
   https://your-domain.com/connect/linkedin/check
   ```
3. Create separate OAuth apps for production (recommended)
4. Never commit `.env.local` to Git

## Still Not Working?

1. Check browser console (F12) for errors
2. Check Symfony logs: `var/log/dev.log`
3. Verify OAuth credentials are correct
4. Try incognito/private browsing mode
5. Check firewall/antivirus settings
6. Read full troubleshooting guide: `docs/OAUTH_TROUBLESHOOTING.md`

## Need Help?

Run the diagnostic command:
```bash
php bin/console app:check-oauth
```

This will show you exactly what needs to be configured.
