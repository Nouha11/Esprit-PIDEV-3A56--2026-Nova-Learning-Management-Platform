# Quick Setup Guide for Team Members

## 🚀 Get Started in 5 Minutes

### Step 1: Clone the Repository
```bash
git clone https://github.com/Nouha11/Pi_web.git
cd Pi_web
```

### Step 2: Install Dependencies
```bash
composer install
```

### Step 3: Setup OAuth Credentials

**Copy the example file:**
```bash
# Windows
copy .env.local.example .env.local

# Linux/Mac
cp .env.local.example .env.local
```

**Get the credentials from your team lead and paste them into `.env.local`**

Your `.env.local` should look like this:
```env
GOOGLE_CLIENT_ID=actual-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=actual-secret-here
LINKEDIN_CLIENT_ID=actual-linkedin-id
LINKEDIN_CLIENT_SECRET=actual-linkedin-secret
```

### Step 4: Setup Database
```bash
# Create database
php bin/console doctrine:database:create

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

# (Optional) Load sample data
php bin/console doctrine:fixtures:load --no-interaction
```

### Step 5: Clear Cache
```bash
php bin/console cache:clear
```

### Step 6: Start the Server
```bash
# Option 1: Symfony CLI (recommended)
symfony server:start

# Option 2: PHP built-in server
php -S localhost:8000 -t public
```

### Step 7: Test It!

1. Open browser: `http://localhost:8000`
2. Go to login page
3. Click "Login with Google" or "Login with LinkedIn"
4. Should work! ✅

## ⚠️ Important Notes

- **Never commit `.env.local`** - It's in `.gitignore` for a reason!
- **OAuth will only work on `localhost:8000` or `127.0.0.1:8000`**
- If you need a different port, ask the team lead to add it to OAuth console

## 🔧 Troubleshooting

### OAuth Not Working?

1. **Check your `.env.local` exists and has credentials**
   ```bash
   # Windows
   type .env.local
   
   # Linux/Mac
   cat .env.local
   ```

2. **Verify OAuth configuration**
   ```bash
   php bin/console app:check-oauth
   ```

3. **Clear cache**
   ```bash
   php bin/console cache:clear
   ```

4. **Make sure you're using the correct URL**
   - ✅ `http://localhost:8000`
   - ✅ `http://127.0.0.1:8000`
   - ❌ `http://192.168.x.x:8000` (won't work)

### Database Issues?

```bash
# Drop and recreate database
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

### Port Already in Use?

If port 8000 is taken, you can use a different port:
```bash
php -S localhost:8001 -t public
```

**But**: OAuth won't work unless the team lead adds that port to OAuth console.

## 📝 Development Workflow

1. **Pull latest changes**
   ```bash
   git pull origin main
   ```

2. **Update dependencies**
   ```bash
   composer install
   ```

3. **Run migrations**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

4. **Clear cache**
   ```bash
   php bin/console cache:clear
   ```

5. **Start coding!** 🎉

## 🆘 Need Help?

- Check `SETUP_FOR_DEVELOPERS.md` for detailed setup
- Check `docs/OAUTH_SETUP_GUIDE.md` for OAuth details
- Ask your team lead for credentials
- Run `php bin/console app:check-oauth` to diagnose issues

## 📚 Useful Commands

```bash
# Check OAuth configuration
php bin/console app:check-oauth

# List all routes
php bin/console debug:router

# Clear cache
php bin/console cache:clear

# Create a new user (if needed)
php bin/console app:create-user

# Check database connection
php bin/console doctrine:database:create --if-not-exists
```

---

**That's it! You're ready to develop! 🚀**
