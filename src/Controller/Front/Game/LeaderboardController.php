<?php

namespace App\Controller\Front\Game;

use App\Repository\StudentProfileRepository;
use App\Service\game\LevelCalculatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/leaderboard')]
class LeaderboardController extends AbstractController
{
    public function __construct(
        private StudentProfileRepository $studentRepository,
        private LevelCalculatorService $levelCalculator
    ) {
    }

    /**
     * Display the leaderboard page
     */
    #[Route('', name: 'front_leaderboard_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('front/game/leaderboard.html.twig');
    }

    /**
     * Get leaderboard data via Ajax
     */
    #[Route('/data', name: 'front_leaderboard_data', methods: ['GET'])]
    public function getData(Request $request): JsonResponse
    {
        $search = $request->query->get('search', '');
        
        // Build query
        $queryBuilder = $this->studentRepository->createQueryBuilder('s')
            ->orderBy('s.totalXP', 'DESC')
            ->addOrderBy('s.totalTokens', 'DESC')
            ->addOrderBy('s.id', 'ASC');

        // Apply search filter if provided
        if (!empty($search)) {
            $queryBuilder
                ->where('s.firstName LIKE :search OR s.lastName LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $students = $queryBuilder->getQuery()->getResult();

        // Build leaderboard data
        $leaderboardData = [];
        $rank = 1;

        foreach ($students as $student) {
            $levelInfo = $this->levelCalculator->calculateLevel($student->getTotalXP());
            
            // Get profile picture URL or null
            $profilePictureUrl = null;
            if ($student->getProfilePicture()) {
                $profilePictureUrl = '/uploads/avatars/' . $student->getProfilePicture();
            }
            
            $leaderboardData[] = [
                'rank' => $rank++,
                'id' => $student->getId(),
                'firstName' => $student->getFirstName(),
                'lastName' => $student->getLastName(),
                'fullName' => $student->getFirstName() . ' ' . $student->getLastName(),
                'xp' => $student->getTotalXP(),
                'tokens' => $student->getTotalTokens(),
                'level' => $levelInfo['level'],
                'levelName' => $levelInfo['name'],
                'progress' => $levelInfo['progress'],
                'badgeColor' => $this->levelCalculator->getLevelBadgeColor($levelInfo['level']),
                'icon' => $this->levelCalculator->getLevelIcon($levelInfo['level']),
                'profilePicture' => $profilePictureUrl,
            ];
        }

        return $this->json([
            'success' => true,
            'data' => $leaderboardData,
            'total' => count($leaderboardData),
        ]);
    }

    /**
     * Get current user's rank
     */
    #[Route('/my-rank', name: 'front_leaderboard_my_rank', methods: ['GET'])]
    public function getMyRank(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user || !$user->getStudentProfile()) {
            return $this->json([
                'success' => false,
                'message' => 'Student profile not found'
            ], 404);
        }

        $student = $user->getStudentProfile();
        
        // Get all students ordered by XP
        $allStudents = $this->studentRepository->createQueryBuilder('s')
            ->orderBy('s.totalXP', 'DESC')
            ->addOrderBy('s.totalTokens', 'DESC')
            ->addOrderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();

        // Find current student's rank
        $rank = 1;
        foreach ($allStudents as $s) {
            if ($s->getId() === $student->getId()) {
                break;
            }
            $rank++;
        }

        $levelInfo = $this->levelCalculator->calculateLevel($student->getTotalXP());

        return $this->json([
            'success' => true,
            'data' => [
                'rank' => $rank,
                'totalPlayers' => count($allStudents),
                'xp' => $student->getTotalXP(),
                'tokens' => $student->getTotalTokens(),
                'level' => $levelInfo['level'],
                'levelName' => $levelInfo['name'],
                'progress' => $levelInfo['progress'],
            ]
        ]);
    }
}
