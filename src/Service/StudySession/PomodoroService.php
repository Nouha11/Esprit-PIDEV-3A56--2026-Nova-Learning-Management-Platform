<?php

namespace App\Service\StudySession;

use App\Entity\StudySession\StudySession;
use Doctrine\ORM\EntityManagerInterface;

class PomodoroService
{
    private const POMODORO_DURATION_MINUTES = 25;
    private const SHORT_BREAK_MINUTES = 5;
    private const LONG_BREAK_MINUTES = 15;
    private const LONG_BREAK_THRESHOLD = 4;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Start a Pomodoro timer for a study session.
     * Returns timer state with 25-minute countdown.
     *
     * @param StudySession $session
     * @return array Timer state with duration and start time
     */
    public function startPomodoro(StudySession $session): array
    {
        return [
            'duration' => self::POMODORO_DURATION_MINUTES * 60, // Convert to seconds
            'startTime' => time(),
            'endTime' => time() + (self::POMODORO_DURATION_MINUTES * 60),
            'pomodoroCount' => $session->getPomodoroCount() ?? 0,
            'status' => 'running'
        ];
    }

    /**
     * Complete a Pomodoro interval and increment the count.
     *
     * @param StudySession $session
     * @return void
     */
    public function completePomodoro(StudySession $session): void
    {
        $currentCount = $session->getPomodoroCount() ?? 0;
        $session->setPomodoroCount($currentCount + 1);
        
        $this->entityManager->persist($session);
        $this->entityManager->flush();
    }

    /**
     * Get the recommended break duration based on completed Pomodoros.
     * Returns 5 minutes for <4 pomodoros, 15 minutes for 4+ pomodoros.
     *
     * @param int $completedPomodoros
     * @return int Break duration in minutes
     */
    public function getBreakDuration(int $completedPomodoros): int
    {
        if ($completedPomodoros >= self::LONG_BREAK_THRESHOLD) {
            return self::LONG_BREAK_MINUTES;
        }
        
        return self::SHORT_BREAK_MINUTES;
    }
}
