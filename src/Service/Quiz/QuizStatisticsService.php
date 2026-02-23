<?php

namespace App\Service\Quiz;

use App\Repository\QuizRepository;
use App\Repository\Quiz\QuestionRepository;
use App\Repository\Quiz\QuizReportRepository;
use Doctrine\ORM\EntityManagerInterface;

class QuizStatisticsService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private QuizRepository $quizRepository,
        private QuestionRepository $questionRepository,
        private QuizReportRepository $quizReportRepository
    ) {
    }

    /**
     * Get comprehensive quiz statistics
     */
    public function getStatistics(): array
    {
        return [
            'overview' => $this->getOverviewStats(),
            'quizzes' => $this->getQuizStats(),
            'questions' => $this->getQuestionStats(),
            'reports' => $this->getReportStats(),
            'topQuizzes' => $this->getTopQuizzes(),
            'recentActivity' => $this->getRecentActivity(),
        ];
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats(): array
    {
        $conn = $this->entityManager->getConnection();

        // Total quizzes
        $totalQuizzes = $conn->fetchOne('SELECT COUNT(*) FROM quiz');

        // Total questions
        $totalQuestions = $conn->fetchOne('SELECT COUNT(*) FROM question');

        // Total reports
        $totalReports = $conn->fetchOne('SELECT COUNT(*) FROM quiz_report');

        // Pending reports
        $pendingReports = $conn->fetchOne(
            "SELECT COUNT(*) FROM quiz_report WHERE status = 'pending'"
        );

        return [
            'totalQuizzes' => (int) $totalQuizzes,
            'totalQuestions' => (int) $totalQuestions,
            'totalReports' => (int) $totalReports,
            'pendingReports' => (int) $pendingReports,
        ];
    }

    /**
     * Get quiz-specific statistics
     */
    private function getQuizStats(): array
    {
        $conn = $this->entityManager->getConnection();

        // Average questions per quiz
        $avgQuestions = $conn->fetchOne(
            'SELECT AVG(question_count) FROM (
                SELECT COUNT(q.id) as question_count 
                FROM quiz qz 
                LEFT JOIN question q ON q.quiz_id = qz.id 
                GROUP BY qz.id
            ) as subquery'
        );

        // Quiz with most questions
        $mostQuestions = $conn->fetchAssociative(
            'SELECT qz.id, qz.title, COUNT(q.id) as question_count 
            FROM quiz qz 
            LEFT JOIN question q ON q.quiz_id = qz.id 
            GROUP BY qz.id 
            ORDER BY question_count DESC 
            LIMIT 1'
        );

        // Quizzes without questions
        $emptyQuizzes = $conn->fetchOne(
            'SELECT COUNT(*) FROM quiz qz 
            LEFT JOIN question q ON q.quiz_id = qz.id 
            WHERE q.id IS NULL'
        );

        return [
            'avgQuestionsPerQuiz' => round((float) $avgQuestions, 1),
            'mostQuestions' => $mostQuestions ?: null,
            'emptyQuizzes' => (int) $emptyQuizzes,
        ];
    }

    /**
     * Get question-specific statistics
     */
    private function getQuestionStats(): array
    {
        $conn = $this->entityManager->getConnection();

        // Questions by difficulty
        $byDifficulty = $conn->fetchAllAssociative(
            'SELECT difficulty, COUNT(*) as count 
            FROM question 
            GROUP BY difficulty'
        );

        // Average XP per question
        $avgXp = $conn->fetchOne('SELECT AVG(xp_value) FROM question');

        // Questions with images
        $withImages = $conn->fetchOne(
            'SELECT COUNT(*) FROM question WHERE image_name IS NOT NULL'
        );

        // Total XP available
        $totalXp = $conn->fetchOne('SELECT SUM(xp_value) FROM question');

        return [
            'byDifficulty' => $byDifficulty,
            'avgXp' => round((float) $avgXp, 0),
            'withImages' => (int) $withImages,
            'totalXp' => (int) $totalXp,
        ];
    }

    /**
     * Get report statistics
     */
    private function getReportStats(): array
    {
        $conn = $this->entityManager->getConnection();

        // Reports by status
        $byStatus = $conn->fetchAllAssociative(
            'SELECT status, COUNT(*) as count 
            FROM quiz_report 
            GROUP BY status'
        );

        // Reports by reason
        $byReason = $conn->fetchAllAssociative(
            'SELECT reason, COUNT(*) as count 
            FROM quiz_report 
            GROUP BY reason 
            ORDER BY count DESC 
            LIMIT 5'
        );

        // Most reported quiz
        $mostReported = $conn->fetchAssociative(
            'SELECT q.id, q.title, COUNT(qr.id) as report_count 
            FROM quiz q 
            INNER JOIN quiz_report qr ON qr.quiz_id = q.id 
            GROUP BY q.id 
            ORDER BY report_count DESC 
            LIMIT 1'
        );

        return [
            'byStatus' => $byStatus,
            'byReason' => $byReason,
            'mostReported' => $mostReported ?: null,
        ];
    }

    /**
     * Get top quizzes by question count
     */
    private function getTopQuizzes(int $limit = 5): array
    {
        $conn = $this->entityManager->getConnection();

        return $conn->fetchAllAssociative(
            'SELECT q.id, q.title, q.description, COUNT(qu.id) as question_count 
            FROM quiz q 
            LEFT JOIN question qu ON qu.quiz_id = q.id 
            GROUP BY q.id 
            ORDER BY question_count DESC 
            LIMIT ' . $limit
        );
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(int $limit = 10): array
    {
        $conn = $this->entityManager->getConnection();

        // Recent reports
        $recentReports = $conn->fetchAllAssociative(
            'SELECT qr.id, qr.reason, qr.status, qr.created_at, 
                    q.title as quiz_title, u.username as reporter 
            FROM quiz_report qr 
            INNER JOIN quiz q ON qr.quiz_id = q.id 
            INNER JOIN user u ON qr.reported_by_id = u.id 
            ORDER BY qr.created_at DESC 
            LIMIT ' . $limit
        );

        return [
            'recentReports' => $recentReports,
        ];
    }

    /**
     * Get difficulty distribution for charts
     */
    public function getDifficultyDistribution(): array
    {
        $conn = $this->entityManager->getConnection();

        $data = $conn->fetchAllAssociative(
            'SELECT difficulty, COUNT(*) as count 
            FROM question 
            GROUP BY difficulty'
        );

        $distribution = [
            'Easy' => 0,
            'Medium' => 0,
            'Hard' => 0,
        ];

        foreach ($data as $row) {
            $distribution[$row['difficulty']] = (int) $row['count'];
        }

        return $distribution;
    }

    /**
     * Get report status distribution for charts
     */
    public function getReportStatusDistribution(): array
    {
        $conn = $this->entityManager->getConnection();

        $data = $conn->fetchAllAssociative(
            'SELECT status, COUNT(*) as count 
            FROM quiz_report 
            GROUP BY status'
        );

        $distribution = [
            'pending' => 0,
            'resolved' => 0,
            'dismissed' => 0,
        ];

        foreach ($data as $row) {
            $distribution[$row['status']] = (int) $row['count'];
        }

        return $distribution;
    }
}
