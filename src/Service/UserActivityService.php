<?php

namespace App\Service;

use App\Entity\users\User;
use App\Entity\users\UserActivity;
use App\Repository\UserActivityRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserActivityService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserActivityRepository $activityRepository
    ) {}

    /**
     * Log a user activity
     */
    public function logActivity(
        User $user,
        string $type,
        string $description,
        ?array $metadata = null,
        ?string $icon = null,
        ?string $color = null
    ): UserActivity {
        $activity = new UserActivity();
        $activity->setUser($user);
        $activity->setActivityType($type);
        $activity->setDescription($description);
        $activity->setMetadata($metadata);
        $activity->setIcon($icon ?? $this->getDefaultIcon($type));
        $activity->setColor($color ?? $this->getDefaultColor($type));

        $this->entityManager->persist($activity);
        $this->entityManager->flush();

        return $activity;
    }

    /**
     * Get recent activities for a user
     */
    public function getRecentActivities(User $user, int $limit = 10): array
    {
        return $this->activityRepository->findRecentByUser($user, $limit);
    }

    /**
     * Get activities by type
     */
    public function getActivitiesByType(User $user, string $type, int $limit = 10): array
    {
        return $this->activityRepository->findByUserAndType($user, $type, $limit);
    }

    /**
     * Get activity statistics
     */
    public function getActivityStats(User $user): array
    {
        $types = [
            'game_played',
            'quiz_completed',
            'course_enrolled',
            'reward_claimed',
            'level_up',
            'badge_earned',
            'xp_earned',
            'tokens_earned',
            'profile_updated',
            'login'
        ];

        $stats = [];
        foreach ($types as $type) {
            $stats[$type] = $this->activityRepository->countByUserAndType($user, $type);
        }

        return $stats;
    }

    /**
     * Get default icon for activity type
     */
    private function getDefaultIcon(string $type): string
    {
        return match($type) {
            'game_played' => 'bi-controller',
            'quiz_completed' => 'bi-lightning-charge',
            'course_enrolled' => 'bi-book',
            'reward_claimed' => 'bi-gift',
            'level_up' => 'bi-arrow-up-circle',
            'badge_earned' => 'bi-award',
            'xp_earned' => 'bi-star',
            'tokens_earned' => 'bi-coin',
            'profile_updated' => 'bi-person-check',
            'login' => 'bi-box-arrow-in-right',
            'logout' => 'bi-box-arrow-right',
            'password_changed' => 'bi-key',
            '2fa_enabled' => 'bi-shield-check',
            '2fa_disabled' => 'bi-shield-x',
            'favorite_added' => 'bi-heart-fill',
            'favorite_removed' => 'bi-heart',
            default => 'bi-circle'
        };
    }

    /**
     * Get default color for activity type
     */
    private function getDefaultColor(string $type): string
    {
        return match($type) {
            'game_played' => 'primary',
            'quiz_completed' => 'purple',
            'course_enrolled' => 'info',
            'reward_claimed' => 'warning',
            'level_up' => 'success',
            'badge_earned' => 'warning',
            'xp_earned' => 'success',
            'tokens_earned' => 'warning',
            'profile_updated' => 'info',
            'login' => 'success',
            'logout' => 'secondary',
            'password_changed' => 'warning',
            '2fa_enabled' => 'success',
            '2fa_disabled' => 'danger',
            'favorite_added' => 'danger',
            'favorite_removed' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Cleanup old activities (keep last 90 days)
     */
    public function cleanupOldActivities(int $daysToKeep = 90): int
    {
        $date = new \DateTime("-{$daysToKeep} days");
        return $this->activityRepository->deleteOlderThan($date);
    }
}
