<?php

namespace App\Service\StudySession;

use App\Entity\StudySession\Course;
use App\Entity\User;
use App\Service\game\LevelCalculatorService;
use App\Service\game\LevelRewardService;
use App\Service\game\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CourseCompletionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenService $tokenService,
        private LevelCalculatorService $levelCalculator,
        private LevelRewardService $levelRewardService,
        private RequestStack $requestStack
    ) {
    }

    /**
     * Complete a course and award rewards to the student
     *
     * @param User $user The student completing the course
     * @param Course $course The course being completed
     * @return void
     */
    public function completeCourse(User $user, Course $course): void
    {
        $studentProfile = $user->getStudentProfile();
        
        if (!$studentProfile) {
            throw new \RuntimeException('Student profile not found');
        }

        // Define reward amounts
        $xpReward = 100; // Base XP for course completion
        $tokenReward = 50; // Base tokens for course completion

        // Store previous level for badge checking
        $previousLevel = $studentProfile->getLevel();

        // Award XP
        $studentProfile->addXP($xpReward);

        // Calculate new level based on total XP
        $levelInfo = $this->levelCalculator->calculateLevel($studentProfile->getTotalXP());
        $studentProfile->setLevel($levelInfo['level']);

        // Award tokens
        $this->tokenService->addTokens($studentProfile, $tokenReward, 'Course completion');

        // Check and award level-based badges/rewards
        $levelRewards = $this->levelRewardService->checkAndAwardLevelRewards($studentProfile, $previousLevel);

        // Persist changes
        $this->entityManager->flush();

        // Add flash messages for rewards
        $session = $this->requestStack->getSession();
        $session->getFlashBag()->add('success', sprintf('Course completed! You earned %d XP!', $xpReward));
        $session->getFlashBag()->add('success', sprintf('You earned %d tokens!', $tokenReward));

        // Add flash messages for level rewards/badges
        foreach ($levelRewards as $reward) {
            $session->getFlashBag()->add(
                'success',
                sprintf(
                    'Level %d milestone reached! You earned the "%s" badge and %d tokens!',
                    $reward['level'],
                    $reward['name'],
                    $reward['tokens']
                )
            );
        }

        // Add level up message if level changed
        if ($levelInfo['level'] > $previousLevel) {
            $session->getFlashBag()->add(
                'success',
                sprintf('Level up! You are now level %d (%s)!', $levelInfo['level'], $levelInfo['name'])
            );
        }
    }
}
