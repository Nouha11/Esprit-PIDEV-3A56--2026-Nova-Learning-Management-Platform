<?php

namespace App\Service\game;

use App\Entity\Gamification\Reward;
use App\Entity\users\StudentProfile;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;

class RewardNotificationService
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private string $senderEmail = 'noreply@nova-platform.com',
        private string $senderName = 'NOVA Platform'
    ) {
    }

    /**
     * Send reward unlock notification email
     */
    public function sendRewardUnlockedEmail(StudentProfile $student, Reward $reward): bool
    {
        try {
            // Get user email - adjust based on your User entity structure
            $userEmail = $this->getUserEmail($student);
            
            if (!$userEmail) {
                $this->logger->warning('Cannot send reward email: No email found for student', [
                    'student_id' => $student->getId()
                ]);
                return false;
            }

            $email = (new TemplatedEmail())
                ->from(new Address($this->senderEmail, $this->senderName))
                ->to($userEmail)
                ->subject('🎉 Congratulations! You\'ve Unlocked a New Reward!')
                ->htmlTemplate('emails/reward_unlocked.html.twig')
                ->context([
                    'student' => $student,
                    'reward' => $reward,
                    'userName' => $student->getFirstName() . ' ' . $student->getLastName(),
                    'rewardName' => $reward->getName(),
                    'rewardType' => $this->formatRewardType($reward->getType()),
                    'rewardDescription' => $reward->getDescription(),
                    'rewardIcon' => $reward->getIcon(),
                    'totalXP' => $student->getTotalXP(),
                    'totalTokens' => $student->getTotalTokens(),
                    'currentLevel' => $student->getLevel(),
                ]);

            $this->mailer->send($email);
            
            $this->logger->info('Reward unlock email sent successfully', [
                'student_id' => $student->getId(),
                'reward_id' => $reward->getId(),
                'email' => $userEmail
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send reward unlock email', [
                'student_id' => $student->getId(),
                'reward_id' => $reward->getId(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get user email from student profile
     * Adjust this method based on your User entity structure
     */
    private function getUserEmail(StudentProfile $student): ?string
    {
        // If StudentProfile has a direct email field
        if (method_exists($student, 'getEmail')) {
            return $student->getEmail();
        }

        // If StudentProfile has a User relationship
        if (method_exists($student, 'getUser')) {
            $user = $student->getUser();
            if ($user && method_exists($user, 'getEmail')) {
                return $user->getEmail();
            }
        }

        return null;
    }

    /**
     * Format reward type for display
     */
    private function formatRewardType(string $type): string
    {
        return match($type) {
            'BADGE' => '🏅 Badge',
            'ACHIEVEMENT' => '🏆 Achievement',
            'BONUS_XP' => '⭐ Bonus XP',
            'BONUS_TOKENS' => '💰 Bonus Tokens',
            default => str_replace('_', ' ', $type)
        };
    }

    /**
     * Send batch reward notifications (for multiple rewards at once)
     */
    public function sendBatchRewardNotifications(StudentProfile $student, array $rewards): int
    {
        $successCount = 0;
        
        foreach ($rewards as $reward) {
            if ($this->sendRewardUnlockedEmail($student, $reward)) {
                $successCount++;
            }
        }

        return $successCount;
    }
}
