# Reward Email Notification - Testing Guide

## Overview
This guide explains how to test the Reward Email Notification system that automatically sends styled HTML emails when users unlock rewards.

## Prerequisites

### 1. Configure Email Settings

You need to configure the `MAILER_DSN` in your `.env` file. Choose one of the following options:

#### Option A: Gmail (Recommended for Testing)
```env
MAILER_DSN=gmail+smtp://your-email@gmail.com:your-app-password@default
```

**Steps to get Gmail App Password:**
1. Go to your Google Account settings
2. Enable 2-Step Verification
3. Go to Security > 2-Step Verification > App passwords
4. Generate a new app password for "Mail"
5. Use that password in the MAILER_DSN

#### Option B: Mailtrap (Best for Development)
```env
MAILER_DSN=smtp://username:password@smtp.mailtrap.io:2525
```

**Steps:**
1. Sign up at https://mailtrap.io (free)
2. Get your SMTP credentials from the inbox
3. Use them in the MAILER_DSN

#### Option C: Local SMTP Server
```env
MAILER_DSN=smtp://localhost:1025
```

Use with MailHog or similar local SMTP server.

### 2. Ensure Students Have Email Addresses

Students need email addresses to receive notifications. Check the database:

```sql
SELECT id, first_name, last_name, email FROM student_profile;
```

If students don't have emails, update them:

```sql
UPDATE student_profile SET email = 'student1@example.com' WHERE id = 1;
UPDATE student_profile SET email = 'student2@example.com' WHERE id = 2;
```

## Testing Methods

### Method 1: Using the Test Command (Easiest)

#### Step 1: List Available Students and Rewards
```bash
php bin/console app:test-reward-email
```

This will display:
- All students with their IDs, names, emails, levels, XP, and tokens
- All rewards with their IDs, names, types, and descriptions

#### Step 2: Send Test Email
```bash
php bin/console app:test-reward-email <studentId> <rewardId>
```

**Example:**
```bash
php bin/console app:test-reward-email 1 1
```

This will:
- Show student and reward details
- Send the email
- Display success or error message

### Method 2: Through the Game System

#### Step 1: Play a Game
1. Log in as a student
2. Go to `/games`
3. Play a game and earn XP/tokens

#### Step 2: Unlock a Reward
The system will automatically:
- Check if you've unlocked any new rewards
- Send an email notification if you have
- Display a success message

### Method 3: Manually Trigger from Controller

You can manually award a reward to test the email:

```php
// In any controller
$student = $studentRepository->find(1);
$reward = $rewardRepository->find(1);

// Award reward and send email
$rewardService->awardRewardToStudent($student, $reward, true);
```

## Email Template Features

The email includes:
- **Responsive Design**: Works on desktop and mobile
- **Dark/Light Mode Support**: Automatically adapts to user's email client theme
- **Reward Details**: Icon, name, type, description
- **Student Stats**: Current level, total XP, total tokens
- **Call-to-Action**: Button linking to "My Rewards" page
- **Professional Footer**: Links to games, rewards, and profile

## Troubleshooting

### Email Not Sending

1. **Check MAILER_DSN configuration**
   ```bash
   php bin/console debug:container --env-vars | findstr MAILER
   ```

2. **Clear cache**
   ```bash
   php bin/console cache:clear
   ```

3. **Check logs**
   ```bash
   tail -f var/log/dev.log
   ```

### Student Has No Email

Error: "Student does not have an email address!"

**Solution:** Update the student's email in the database or admin panel.

### SMTP Authentication Failed

**For Gmail:**
- Make sure you're using an App Password, not your regular password
- Enable "Less secure app access" if using regular password (not recommended)

**For Mailtrap:**
- Verify your username and password are correct
- Check if your account is active

### Email Goes to Spam

- Use a real email service (not null://)
- Configure SPF/DKIM records for production
- Test with Mailtrap first to verify email content

## Testing Checklist

- [ ] MAILER_DSN is configured in .env
- [ ] Students have valid email addresses
- [ ] Test command lists students and rewards correctly
- [ ] Test email sends successfully
- [ ] Email arrives in inbox (check spam folder)
- [ ] Email displays correctly on desktop
- [ ] Email displays correctly on mobile
- [ ] Dark mode styling works (if email client supports it)
- [ ] All links in email work correctly
- [ ] Reward details are accurate
- [ ] Student stats are correct

## Production Considerations

Before deploying to production:

1. **Use a Real Email Service**
   - SendGrid, Mailgun, Amazon SES, etc.
   - Update MAILER_DSN accordingly

2. **Configure Email From Address**
   Edit `config/packages/mailer.yaml`:
   ```yaml
   framework:
       mailer:
           dsn: '%env(MAILER_DSN)%'
           envelope:
               sender: 'noreply@yourdomain.com'
   ```

3. **Add Email Queue**
   Use Symfony Messenger to queue emails:
   ```php
   $this->notificationService->sendRewardUnlockedEmail($student, $reward);
   ```

4. **Monitor Email Delivery**
   - Set up email delivery monitoring
   - Track bounce rates
   - Handle unsubscribe requests

## Example Output

When running the test command successfully:

```
Test Reward Email Notification
===============================

Sending Test Email
------------------

 ---------- --------------------------------
  Field      Value
 ---------- --------------------------------
  Student    John Doe
  Email      john.doe@example.com
  Level      5
  XP         1250
  Tokens     45
  Reward     Master Badge
  Type       BADGE
 ---------- --------------------------------

 [OK] Email sent successfully to john.doe@example.com!

 ! [NOTE] Check your email inbox (or spam folder) for the reward notification.
```

## Support

If you encounter issues:
1. Check the Symfony logs: `var/log/dev.log`
2. Verify email configuration: `php bin/console debug:config framework mailer`
3. Test SMTP connection manually
4. Review the email template: `templates/emails/reward_unlocked.html.twig`
