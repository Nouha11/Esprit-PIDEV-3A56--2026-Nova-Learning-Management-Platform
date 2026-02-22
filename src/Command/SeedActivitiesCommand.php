<?php

namespace App\Command;

use App\Entity\users\User;
use App\Service\UserActivityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-activities',
    description: 'Seed sample user activities for testing',
)]
class SeedActivitiesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserActivityService $activityService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Get all users
        $users = $this->entityManager->getRepository(User::class)->findAll();

        if (empty($users)) {
            $io->error('No users found in the database.');
            return Command::FAILURE;
        }

        $io->info('Seeding activities for ' . count($users) . ' users...');

        foreach ($users as $user) {
            $this->seedActivitiesForUser($user, $io);
        }

        $io->success('Sample activities seeded successfully!');

        return Command::SUCCESS;
    }

    private function seedActivitiesForUser(User $user, SymfonyStyle $io): void
    {
        $io->text('Seeding activities for user: ' . $user->getUsername());

        // Sample activities
        $activities = [
            [
                'type' => 'login',
                'description' => 'Logged into the platform',
                'metadata' => null,
            ],
            [
                'type' => 'game_played',
                'description' => 'Played Math Challenge game',
                'metadata' => ['game_name' => 'Math Challenge', 'score' => 850],
            ],
            [
                'type' => 'xp_earned',
                'description' => 'Earned XP from completing a game',
                'metadata' => ['xp' => 50],
            ],
            [
                'type' => 'quiz_completed',
                'description' => 'Completed Science Quiz',
                'metadata' => ['quiz_name' => 'Science Quiz', 'score' => 90],
            ],
            [
                'type' => 'tokens_earned',
                'description' => 'Earned tokens from quiz completion',
                'metadata' => ['tokens' => 25],
            ],
            [
                'type' => 'level_up',
                'description' => 'Leveled up to Level 5!',
                'metadata' => ['level' => 5, 'xp' => 100],
            ],
            [
                'type' => 'badge_earned',
                'description' => 'Earned "Quiz Master" badge',
                'metadata' => ['badge_name' => 'Quiz Master'],
            ],
            [
                'type' => 'reward_claimed',
                'description' => 'Claimed "Premium Course Access" reward',
                'metadata' => ['reward_name' => 'Premium Course Access', 'tokens' => 50],
            ],
            [
                'type' => 'course_enrolled',
                'description' => 'Enrolled in Advanced Mathematics course',
                'metadata' => ['course_name' => 'Advanced Mathematics'],
            ],
            [
                'type' => 'profile_updated',
                'description' => 'Updated profile information',
                'metadata' => null,
            ],
            [
                'type' => '2fa_enabled',
                'description' => 'Enabled Two-Factor Authentication',
                'metadata' => null,
            ],
            [
                'type' => 'favorite_added',
                'description' => 'Added Word Puzzle to favorites',
                'metadata' => ['game_name' => 'Word Puzzle'],
            ],
        ];

        // Create activities with different timestamps
        $daysAgo = 10;
        foreach ($activities as $activityData) {
            $activity = $this->activityService->logActivity(
                $user,
                $activityData['type'],
                $activityData['description'],
                $activityData['metadata']
            );

            // Set created date to simulate past activities
            $createdAt = new \DateTime("-{$daysAgo} days");
            $activity->setCreatedAt($createdAt);
            $this->entityManager->flush();

            $daysAgo--;
            if ($daysAgo < 0) $daysAgo = 0;
        }

        $io->text('  ✓ Created ' . count($activities) . ' activities');
    }
}
