<?php

namespace App\Service\StudySession;

use App\Entity\StudySession\StudySession;
use App\Entity\users\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Psr\Log\LoggerInterface;

class NotificationService
{
    public function __construct(
        private MailerInterface $mailer,
        private MessageBusInterface $messageBus,
        private AnalyticsService $analyticsService,
        private StreakService $streakService,
        private LoggerInterface $logger,
        private string $fromEmail
    ) {
    }

    /**
     * Send session reminder email 30 minutes before session
     * Queues email for async sending via Messenger
     *
     * @param StudySession $session
     * @return void
     */
    public function sendSessionReminder(StudySession $session): void
    {
        $user = $session->getUser();
        
        // Check user notification preferences
        if (!$this->shouldSendNotification($user)) {
            $this->logger->info('Notification skipped for user due to preferences', [
                'user_id' => $user->getId(),
                'notification_type' => 'session_reminder'
            ]);
            return;
        }

        try {
            $planning = $session->getPlanning();
            $course = $planning?->getCourse();
            
            $email = (new Email())
                ->from($this->fromEmail)
                ->to($user->getEmail())
                ->subject('Study Session Reminder')
                ->html($this->renderSessionReminderTemplate(
                    $user->getUsername(),
                    $course?->getCourseName() ?? 'Unknown Course',
                    $session->getDuration() ?? 0,
                    $session->getStartedAt() ?? new \DateTimeImmutable()
                ));

            // Queue email for async sending
            $this->messageBus->dispatch(new SendEmailMessage($email));
            
            $this->logger->info('Session reminder queued', [
                'user_id' => $user->getId(),
                'session_id' => $session->getId()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to queue session reminder', [
                'user_id' => $user->getId(),
                'session_id' => $session->getId(),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send weekly progress report email
     * Includes total time, XP, sessions completed, and current streak
     *
     * @param User $user
     * @return void
     */
    public function sendWeeklyProgressReport(User $user): void
    {
        // Check user notification preferences
        if (!$this->shouldSendNotification($user)) {
            $this->logger->info('Notification skipped for user due to preferences', [
                'user_id' => $user->getId(),
                'notification_type' => 'weekly_report'
            ]);
            return;
        }

        try {
            // Calculate date range for the past week
            $end = new \DateTimeImmutable();
            $start = $end->modify('-7 days');

            // Gather analytics data
            $totalTime = $this->analyticsService->getTotalStudyTime($user, $start, $end);
            $totalXP = $this->analyticsService->getTotalXP($user, $start, $end);
            $currentStreak = $this->streakService->getCurrentStreak($user);
            
            // Count completed sessions
            $completionRate = $this->analyticsService->getCompletionRate($user, $start, $end);

            $email = (new Email())
                ->from($this->fromEmail)
                ->to($user->getEmail())
                ->subject('Your Weekly Study Progress Report')
                ->html($this->renderWeeklyReportTemplate(
                    $user->getUsername(),
                    $totalTime,
                    $totalXP,
                    $currentStreak,
                    $completionRate
                ));

            // Queue email for async sending
            $this->messageBus->dispatch(new SendEmailMessage($email));
            
            $this->logger->info('Weekly progress report queued', [
                'user_id' => $user->getId(),
                'total_time' => $totalTime,
                'total_xp' => $totalXP,
                'streak' => $currentStreak
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to queue weekly progress report', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send achievement notification for streak milestones
     * Supports 7, 30, and 100-day streaks
     *
     * @param User $user
     * @param string $achievementType ('7_day', '30_day', '100_day')
     * @return void
     */
    public function sendAchievementNotification(User $user, string $achievementType): void
    {
        // Check user notification preferences
        if (!$this->shouldSendNotification($user)) {
            $this->logger->info('Notification skipped for user due to preferences', [
                'user_id' => $user->getId(),
                'notification_type' => 'achievement',
                'achievement_type' => $achievementType
            ]);
            return;
        }

        try {
            $streakDays = match($achievementType) {
                '7_day' => 7,
                '30_day' => 30,
                '100_day' => 100,
                default => 0
            };

            if ($streakDays === 0) {
                $this->logger->warning('Invalid achievement type', [
                    'achievement_type' => $achievementType
                ]);
                return;
            }

            $email = (new Email())
                ->from($this->fromEmail)
                ->to($user->getEmail())
                ->subject("🎉 Achievement Unlocked: {$streakDays}-Day Study Streak!")
                ->html($this->renderAchievementTemplate(
                    $user->getUsername(),
                    $streakDays
                ));

            // Queue email for async sending
            $this->messageBus->dispatch(new SendEmailMessage($email));
            
            $this->logger->info('Achievement notification queued', [
                'user_id' => $user->getId(),
                'achievement_type' => $achievementType,
                'streak_days' => $streakDays
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to queue achievement notification', [
                'user_id' => $user->getId(),
                'achievement_type' => $achievementType,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check if notifications should be sent to user
     * Returns true if user has notifications enabled (or field doesn't exist yet)
     *
     * @param User $user
     * @return bool
     */
    private function shouldSendNotification(User $user): bool
    {
        // Check if the emailNotificationsEnabled method exists
        // This field will be added in task 45.1
        if (method_exists($user, 'isEmailNotificationsEnabled')) {
            return $user->isEmailNotificationsEnabled();
        }
        
        // Default to true if field doesn't exist yet
        return true;
    }

    /**
     * Render session reminder email template
     *
     * @param string $username
     * @param string $courseName
     * @param int $duration
     * @param \DateTimeInterface $startTime
     * @return string
     */
    private function renderSessionReminderTemplate(
        string $username,
        string $courseName,
        int $duration,
        \DateTimeInterface $startTime
    ): string {
        $formattedTime = $startTime->format('g:i A');
        $formattedDate = $startTime->format('l, F j, Y');
        
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .details { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #4CAF50; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>📚 Study Session Reminder</h1>
                </div>
                <div class="content">
                    <p>Hi {$username},</p>
                    <p>This is a friendly reminder that your study session is starting soon!</p>
                    
                    <div class="details">
                        <h3>Session Details:</h3>
                        <p><strong>Course:</strong> {$courseName}</p>
                        <p><strong>Duration:</strong> {$duration} minutes</p>
                        <p><strong>Start Time:</strong> {$formattedTime}</p>
                        <p><strong>Date:</strong> {$formattedDate}</p>
                    </div>
                    
                    <p>Get ready to focus and make the most of your study time! 💪</p>
                </div>
                <div class="footer">
                    <p>You're receiving this email because you have study session reminders enabled.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Render weekly progress report email template
     *
     * @param string $username
     * @param int $totalTime
     * @param int $totalXP
     * @param int $currentStreak
     * @param float $completionRate
     * @return string
     */
    private function renderWeeklyReportTemplate(
        string $username,
        int $totalTime,
        int $totalXP,
        int $currentStreak,
        float $completionRate
    ): string {
        $hours = floor($totalTime / 60);
        $minutes = $totalTime % 60;
        $timeFormatted = $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
        
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2196F3; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .stats { display: flex; flex-wrap: wrap; gap: 10px; margin: 20px 0; }
                .stat-box { flex: 1; min-width: 120px; background-color: white; padding: 15px; text-align: center; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .stat-value { font-size: 24px; font-weight: bold; color: #2196F3; }
                .stat-label { font-size: 12px; color: #666; margin-top: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>📊 Your Weekly Progress Report</h1>
                </div>
                <div class="content">
                    <p>Hi {$username},</p>
                    <p>Here's a summary of your study activity for the past week:</p>
                    
                    <div class="stats">
                        <div class="stat-box">
                            <div class="stat-value">{$timeFormatted}</div>
                            <div class="stat-label">Total Study Time</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value">{$totalXP}</div>
                            <div class="stat-label">XP Earned</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value">{$currentStreak}</div>
                            <div class="stat-label">Current Streak</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value">{$completionRate}%</div>
                            <div class="stat-label">Completion Rate</div>
                        </div>
                    </div>
                    
                    <p>Keep up the great work! Consistency is key to achieving your learning goals. 🎯</p>
                </div>
                <div class="footer">
                    <p>You're receiving this weekly report because you have progress notifications enabled.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Render achievement notification email template
     *
     * @param string $username
     * @param int $streakDays
     * @return string
     */
    private function renderAchievementTemplate(string $username, int $streakDays): string
    {
        $emoji = match($streakDays) {
            7 => '🌟',
            30 => '🏆',
            100 => '👑',
            default => '🎉'
        };
        
        $message = match($streakDays) {
            7 => "You've maintained a 7-day study streak! You're building a great habit.",
            30 => "Incredible! You've reached a 30-day study streak. Your dedication is inspiring!",
            100 => "Legendary! You've achieved a 100-day study streak. You're a true champion!",
            default => "You've reached a study streak milestone!"
        };
        
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #FF9800; color: white; padding: 30px; text-align: center; }
                .content { padding: 30px; background-color: #f9f9f9; text-align: center; }
                .achievement { background-color: white; padding: 30px; margin: 20px 0; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
                .emoji { font-size: 64px; margin: 20px 0; }
                .streak-number { font-size: 48px; font-weight: bold; color: #FF9800; margin: 10px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎉 Achievement Unlocked!</h1>
                </div>
                <div class="content">
                    <p>Congratulations, {$username}!</p>
                    
                    <div class="achievement">
                        <div class="emoji">{$emoji}</div>
                        <div class="streak-number">{$streakDays}-Day Streak</div>
                        <p>{$message}</p>
                    </div>
                    
                    <p>Keep the momentum going and continue your learning journey!</p>
                </div>
                <div class="footer">
                    <p>You're receiving this achievement notification because you've reached a study streak milestone.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}
