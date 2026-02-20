# Deployment Checklist - Study Session Enhancement

This document provides a comprehensive checklist for deploying the Study Session Enhancement feature to production environments.

## Table of Contents

- [Pre-Deployment Checklist](#pre-deployment-checklist)
- [Database Migration Steps](#database-migration-steps)
- [Environment Variable Configuration](#environment-variable-configuration)
- [File Upload Directory Setup](#file-upload-directory-setup)
- [Symfony Messenger Configuration](#symfony-messenger-configuration)
- [Cron Job Configuration](#cron-job-configuration)
- [Production Optimization](#production-optimization)
- [Post-Deployment Verification](#post-deployment-verification)
- [Rollback Procedure](#rollback-procedure)

## Pre-Deployment Checklist

### Code Preparation

- [ ] All code changes committed and pushed to version control
- [ ] All tests passing (`php bin/phpunit`)
- [ ] Code reviewed and approved
- [ ] Dependencies updated (`composer install --no-dev --optimize-autoloader`)
- [ ] Frontend assets built (`npm run build`)
- [ ] Documentation updated (README.md, DEPLOYMENT.md)

### Backup

- [ ] Database backup created
  ```bash
  mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
  ```
- [ ] File uploads backed up
  ```bash
  tar -czf uploads_backup_$(date +%Y%m%d_%H%M%S).tar.gz public/uploads/
  ```
- [ ] Environment configuration backed up
  ```bash
  cp .env .env.backup_$(date +%Y%m%d_%H%M%S)
  ```

### Infrastructure

- [ ] Server meets minimum requirements (PHP 8.1+, MySQL 8.0+)
- [ ] Required PHP extensions installed (pdo_mysql, intl, mbstring, xml, curl, gd)
- [ ] Sufficient disk space for file uploads (recommend 10GB minimum)
- [ ] SSL certificate configured for HTTPS
- [ ] Firewall rules configured for database and mail server access

## Database Migration Steps

### 1. Review Pending Migrations

```bash
# Check migration status
php bin/console doctrine:migrations:status

# List pending migrations
php bin/console doctrine:migrations:list
```

### 2. Test Migrations in Staging

```bash
# Dry run to see SQL that will be executed
php bin/console doctrine:migrations:migrate --dry-run

# Execute migrations in staging environment first
php bin/console doctrine:migrations:migrate --no-interaction
```

### 3. Execute Production Migrations

```bash
# Put application in maintenance mode
php bin/console app:maintenance:enable

# Backup database before migration
mysqldump -u username -p database_name > pre_migration_backup.sql

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Verify migration success
php bin/console doctrine:migrations:status

# Exit maintenance mode
php bin/console app:maintenance:disable
```

### 4. Migration Rollback (if needed)

```bash
# Rollback to previous version
php bin/console doctrine:migrations:migrate prev --no-interaction

# Or restore from backup
mysql -u username -p database_name < pre_migration_backup.sql
```

### Key Database Changes

The following tables and columns will be created/modified:

**New Tables:**
- `tag` - Study session tags
- `note` - Study session notes
- `resource` - PDF resources
- `study_streak` - User study streaks
- `study_session_tag` - Many-to-many join table

**Modified Tables:**
- `study_session` - Added columns: mood, energy_level, break_duration, break_count, pomodoro_count, completed_at

**Indexes Created:**
- `tag.name` - For tag lookups
- `note.study_session_id` - For note queries
- `resource.study_session_id` - For resource queries
- `study_session.status` - For filtering by status
- `study_session.scheduled_at` - For date range queries
- `study_session.completed_at` - For analytics queries

## Environment Variable Configuration

### 1. Copy Environment Template

```bash
cp .env.example .env.prod
```

### 2. Configure Required Variables

Edit `.env.prod` with production values:

#### Application Configuration
```env
APP_ENV=prod
APP_SECRET=<generate-secure-random-string>
APP_DEBUG=0
```

Generate secure APP_SECRET:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

#### Database Configuration
```env
DATABASE_URL="mysql://prod_user:secure_password@db_host:3306/prod_database?serverVersion=8.0.32&charset=utf8mb4"
```

**Security Notes:**
- Use a dedicated database user with minimal privileges
- Use strong password (16+ characters, mixed case, numbers, symbols)
- Restrict database access to application server IP only

#### Mailer Configuration
```env
# Production: Use SMTP server, not Gmail
MAILER_DSN=smtp://smtp_user:smtp_password@smtp.example.com:587
```

**Recommended SMTP Providers:**
- SendGrid: `smtp://apikey:YOUR_API_KEY@smtp.sendgrid.net:587`
- Mailgun: `smtp://postmaster@domain:password@smtp.mailgun.org:587`
- Amazon SES: `ses+smtp://ACCESS_KEY:SECRET_KEY@default?region=us-east-1`

#### External API Keys
```env
# YouTube Data API v3
YOUTUBE_API_KEY=<your-production-youtube-api-key>

# OpenWeatherMap API (Optional)
OPENWEATHER_API_KEY=<your-production-weather-api-key>

# OpenAI API
OPENAI_API_KEY=<your-production-openai-api-key>
```

**API Key Security:**
- Use separate API keys for production
- Set up API key restrictions (IP whitelist, referrer restrictions)
- Monitor API usage and set up billing alerts
- Rotate keys periodically

#### File Upload Configuration
```env
UPLOAD_DIRECTORY=uploads/study_sessions
```

#### Messenger Transport
```env
# Production: Use Redis or RabbitMQ for better performance
MESSENGER_TRANSPORT_DSN=redis://redis_host:6379/messages
# Or RabbitMQ:
# MESSENGER_TRANSPORT_DSN=amqp://user:password@rabbitmq_host:5672/%2f/messages
```

### 3. Validate Configuration

```bash
# Check configuration
php bin/console debug:config

# Verify database connection
php bin/console doctrine:query:sql "SELECT 1"

# Test mailer configuration
php bin/console mailer:test your-email@example.com
```

## File Upload Directory Setup

### 1. Create Upload Directories

```bash
# Create directory structure
mkdir -p public/uploads/study_sessions
mkdir -p public/uploads/rewards
mkdir -p public/uploads/books

# Set proper ownership (adjust user/group for your server)
chown -R www-data:www-data public/uploads

# Set proper permissions
chmod -R 775 public/uploads
```

### 2. Configure Web Server

#### Apache (.htaccess)

Create `public/uploads/.htaccess`:
```apache
# Deny access to PHP files
<FilesMatch "\.php$">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Allow only specific file types
<FilesMatch "\.(pdf|jpg|jpeg|png|gif)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>
```

#### Nginx

Add to server block:
```nginx
location /uploads/ {
    # Deny PHP execution
    location ~ \.php$ {
        deny all;
    }
    
    # Allow only specific file types
    location ~* \.(pdf|jpg|jpeg|png|gif)$ {
        try_files $uri =404;
    }
}
```

### 3. Set Up File Size Limits

#### PHP Configuration (php.ini)
```ini
upload_max_filesize = 10M
post_max_size = 12M
memory_limit = 256M
max_execution_time = 300
```

#### Nginx Configuration
```nginx
client_max_body_size 10M;
```

#### Apache Configuration (.htaccess)
```apache
php_value upload_max_filesize 10M
php_value post_max_size 12M
```

### 4. Configure Disk Space Monitoring

```bash
# Set up disk space alert (example using cron)
# Add to crontab:
0 */6 * * * df -h /path/to/uploads | awk 'NR==2 {if ($5+0 > 80) print "Warning: Upload directory is " $5 " full"}' | mail -s "Disk Space Alert" admin@example.com
```

## Symfony Messenger Configuration

### 1. Choose Transport

#### Option A: Doctrine (Simple, no additional services)

`config/packages/messenger.yaml`:
```yaml
framework:
    messenger:
        failure_transport: failed
        
        transports:
            async:
                dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                options:
                    auto_setup: true
                retry_strategy:
                    max_retries: 3
                    delay: 1000
                    multiplier: 2
                    max_delay: 0
            
            failed: 'doctrine://default?queue_name=failed'
        
        routing:
            'App\Message\SendEmailMessage': async
```

#### Option B: Redis (Recommended for production)

Install Redis:
```bash
# Ubuntu/Debian
sudo apt-get install redis-server

# Start Redis
sudo systemctl start redis
sudo systemctl enable redis
```

Update `.env.prod`:
```env
MESSENGER_TRANSPORT_DSN=redis://localhost:6379/messages
```

#### Option C: RabbitMQ (Best for high-volume)

Install RabbitMQ:
```bash
# Ubuntu/Debian
sudo apt-get install rabbitmq-server

# Start RabbitMQ
sudo systemctl start rabbitmq-server
sudo systemctl enable rabbitmq-server

# Create user
sudo rabbitmqctl add_user symfony_user secure_password
sudo rabbitmqctl set_permissions -p / symfony_user ".*" ".*" ".*"
```

Update `.env.prod`:
```env
MESSENGER_TRANSPORT_DSN=amqp://symfony_user:secure_password@localhost:5672/%2f/messages
```

### 2. Set Up Message Consumer

#### Systemd Service (Recommended)

Create `/etc/systemd/system/messenger-worker.service`:
```ini
[Unit]
Description=Symfony Messenger Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/html/project
ExecStart=/usr/bin/php /var/www/html/project/bin/console messenger:consume async --time-limit=3600 --memory-limit=128M
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl daemon-reload
sudo systemctl enable messenger-worker
sudo systemctl start messenger-worker
sudo systemctl status messenger-worker
```

#### Supervisor (Alternative)

Install Supervisor:
```bash
sudo apt-get install supervisor
```

Create `/etc/supervisor/conf.d/messenger-worker.conf`:
```ini
[program:messenger-worker]
command=php /var/www/html/project/bin/console messenger:consume async --time-limit=3600 --memory-limit=128M
user=www-data
numprocs=2
autostart=true
autorestart=true
process_name=%(program_name)s_%(process_num)02d
stderr_logfile=/var/log/messenger-worker.err.log
stdout_logfile=/var/log/messenger-worker.out.log
```

Reload Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start messenger-worker:*
```

### 3. Monitor Message Queue

```bash
# Check queue statistics
php bin/console messenger:stats

# View failed messages
php bin/console messenger:failed:show

# Retry failed messages
php bin/console messenger:failed:retry
```

## Cron Job Configuration

### 1. Edit Crontab

```bash
sudo crontab -e -u www-data
```

### 2. Add Cron Jobs

```bash
# Study Session Enhancement Background Jobs

# Send session reminders every 5 minutes
*/5 * * * * cd /var/www/html/project && php bin/console app:study-session:send-reminders >> /var/log/cron-reminders.log 2>&1

# Check and reset streaks daily at midnight
0 0 * * * cd /var/www/html/project && php bin/console app:study-session:check-streaks >> /var/log/cron-streaks.log 2>&1

# Check and send achievement notifications daily at 00:05
5 0 * * * cd /var/www/html/project && php bin/console app:study-session:check-achievements >> /var/log/cron-achievements.log 2>&1

# Send weekly progress reports every Sunday at 23:59
59 23 * * 0 cd /var/www/html/project && php bin/console app:study-session:send-weekly-reports >> /var/log/cron-weekly-reports.log 2>&1

# Clear expired cache entries daily at 3 AM
0 3 * * * cd /var/www/html/project && php bin/console cache:pool:clear cache.app >> /var/log/cron-cache.log 2>&1

# Clean up old log files weekly
0 4 * * 0 find /var/www/html/project/var/log -name "*.log" -mtime +30 -delete
```

### 3. Set Up Log Rotation

Create `/etc/logrotate.d/symfony-cron`:
```
/var/log/cron-*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    missingok
    create 0640 www-data www-data
}
```

### 4. Verify Cron Jobs

```bash
# List cron jobs
sudo crontab -l -u www-data

# Check cron logs
sudo tail -f /var/log/cron-reminders.log
sudo tail -f /var/log/cron-streaks.log
```

### 5. Windows Task Scheduler (Alternative)

For Windows servers, create scheduled tasks:

```powershell
# Send session reminders (every 5 minutes)
$action = New-ScheduledTaskAction -Execute "C:\php\php.exe" -Argument "C:\inetpub\wwwroot\project\bin\console app:study-session:send-reminders"
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 5) -RepetitionDuration ([TimeSpan]::MaxValue)
Register-ScheduledTask -TaskName "StudySessionReminders" -Action $action -Trigger $trigger -User "SYSTEM"

# Check streaks (daily at midnight)
$action = New-ScheduledTaskAction -Execute "C:\php\php.exe" -Argument "C:\inetpub\wwwroot\project\bin\console app:study-session:check-streaks"
$trigger = New-ScheduledTaskTrigger -Daily -At "00:00"
Register-ScheduledTask -TaskName "StudySessionStreaks" -Action $action -Trigger $trigger -User "SYSTEM"
```

## Production Optimization

### 1. Optimize Composer Autoloader

```bash
composer install --no-dev --optimize-autoloader --classmap-authoritative
```

### 2. Clear and Warm Up Cache

```bash
# Clear all caches
php bin/console cache:clear --env=prod --no-debug

# Warm up cache
php bin/console cache:warmup --env=prod
```

### 3. Enable OPcache

Edit `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

### 4. Configure APCu (if available)

```ini
apc.enabled=1
apc.shm_size=64M
apc.ttl=7200
apc.enable_cli=0
```

### 5. Optimize Database

```sql
-- Analyze tables for query optimization
ANALYZE TABLE study_session, tag, note, resource, study_streak;

-- Optimize tables
OPTIMIZE TABLE study_session, tag, note, resource, study_streak;
```

### 6. Set Up CDN for Static Assets

Configure CDN for:
- JavaScript files (`public/js/`)
- CSS files (`public/assets/css/`)
- Images (`public/assets/images/`)
- Uploaded files (`public/uploads/`)

### 7. Enable HTTP/2 and Compression

#### Nginx
```nginx
# Enable HTTP/2
listen 443 ssl http2;

# Enable gzip compression
gzip on;
gzip_vary on;
gzip_proxied any;
gzip_comp_level 6;
gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss;
```

#### Apache
```apache
# Enable HTTP/2 (requires mod_http2)
Protocols h2 http/1.1

# Enable compression (requires mod_deflate)
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>
```

## Post-Deployment Verification

### 1. Smoke Tests

```bash
# Test homepage
curl -I https://your-domain.com/

# Test authentication
curl -I https://your-domain.com/login

# Test API endpoints
curl -I https://your-domain.com/analytics
```

### 2. Verify Database Migrations

```bash
php bin/console doctrine:migrations:status
php bin/console doctrine:schema:validate
```

### 3. Check Background Jobs

```bash
# Verify cron jobs are running
sudo tail -f /var/log/cron-reminders.log

# Check messenger worker status
sudo systemctl status messenger-worker
# Or for Supervisor:
sudo supervisorctl status messenger-worker:*
```

### 4. Test File Uploads

- Navigate to `/study-session/new`
- Create a study session
- Upload a PDF resource
- Verify file is stored in `public/uploads/study_sessions/`
- Download the file and verify integrity

### 5. Test External Integrations

- **YouTube Search**: Navigate to `/study-session/integration/youtube/search`
- **Wikipedia Search**: Navigate to `/study-session/integration/wikipedia/search`
- **AI Recommendations**: Navigate to `/study-session/integration/ai/recommendations`

### 6. Monitor Error Logs

```bash
# Application logs
tail -f var/log/prod.log

# Web server logs
sudo tail -f /var/log/nginx/error.log
# Or Apache:
sudo tail -f /var/log/apache2/error.log

# PHP-FPM logs
sudo tail -f /var/log/php8.1-fpm.log
```

### 7. Performance Testing

```bash
# Install Apache Bench
sudo apt-get install apache2-utils

# Test homepage performance
ab -n 1000 -c 10 https://your-domain.com/

# Test analytics endpoint
ab -n 100 -c 5 https://your-domain.com/analytics
```

### 8. Security Scan

```bash
# Check for security vulnerabilities
composer audit

# Scan for outdated dependencies
composer outdated
```

## Rollback Procedure

### 1. Quick Rollback (Code Only)

```bash
# Revert to previous Git commit
git revert HEAD
git push origin main

# Or checkout previous version
git checkout <previous-commit-hash>

# Clear cache
php bin/console cache:clear --env=prod
```

### 2. Full Rollback (Code + Database)

```bash
# Stop application
php bin/console app:maintenance:enable

# Restore database backup
mysql -u username -p database_name < backup_YYYYMMDD_HHMMSS.sql

# Revert code
git checkout <previous-commit-hash>

# Clear cache
php bin/console cache:clear --env=prod

# Restart services
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx

# Exit maintenance mode
php bin/console app:maintenance:disable
```

### 3. Restore File Uploads

```bash
# Extract backup
tar -xzf uploads_backup_YYYYMMDD_HHMMSS.tar.gz -C public/

# Verify permissions
chown -R www-data:www-data public/uploads
chmod -R 775 public/uploads
```

## Monitoring and Maintenance

### 1. Set Up Application Monitoring

- Configure error tracking (e.g., Sentry, Rollbar)
- Set up uptime monitoring (e.g., Pingdom, UptimeRobot)
- Monitor API usage and costs
- Track database performance

### 2. Regular Maintenance Tasks

- **Daily**: Review error logs
- **Weekly**: Check disk space, review failed message queue
- **Monthly**: Update dependencies, rotate API keys, review performance metrics
- **Quarterly**: Security audit, load testing, backup restoration test

### 3. Backup Strategy

- **Database**: Daily automated backups, retained for 30 days
- **File Uploads**: Weekly backups, retained for 90 days
- **Configuration**: Version controlled, backed up with code
- **Test Restoration**: Monthly backup restoration tests

## Support and Troubleshooting

### Common Issues

**Issue**: Migrations fail
- Check database user permissions
- Verify database connection
- Review migration SQL in dry-run mode
- Check for conflicting data

**Issue**: File uploads fail
- Verify directory permissions (775)
- Check PHP upload limits
- Verify disk space
- Check web server configuration

**Issue**: Background jobs not running
- Verify cron jobs are active: `sudo crontab -l -u www-data`
- Check cron logs for errors
- Verify messenger worker is running
- Check message transport connectivity

**Issue**: API integrations failing
- Verify API keys are correct
- Check API rate limits and quotas
- Review API error logs
- Test API connectivity manually

### Emergency Contacts

- **System Administrator**: [admin@example.com]
- **Database Administrator**: [dba@example.com]
- **Development Team**: [dev-team@example.com]
- **On-Call Support**: [+1-XXX-XXX-XXXX]

## Deployment Sign-Off

- [ ] All checklist items completed
- [ ] Smoke tests passed
- [ ] Performance acceptable
- [ ] Monitoring configured
- [ ] Team notified of deployment
- [ ] Documentation updated
- [ ] Rollback procedure tested and ready

**Deployed By**: _______________  
**Date**: _______________  
**Version**: _______________  
**Sign-Off**: _______________
