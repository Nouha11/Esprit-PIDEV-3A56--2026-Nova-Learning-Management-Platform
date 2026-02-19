<?php

namespace App\Controller;

use App\Repository\StudentProfileRepository;
use App\Service\game\LevelCalculatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private StudentProfileRepository $studentRepository,
        private LevelCalculatorService $levelCalculator
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $user = $this->getUser();

        // Get top 3 students by XP this month
        $topStudents = $this->studentRepository->createQueryBuilder('s')
            ->orderBy('s.totalXP', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        // Calculate level info for each student
        $topStudentsWithLevels = [];
        foreach ($topStudents as $student) {
            $levelInfo = $this->levelCalculator->calculateLevel($student->getTotalXP());
            $topStudentsWithLevels[] = [
                'student' => $student,
                'levelInfo' => $levelInfo,
            ];
        }

        return $this->render('base.html.twig', [
            'user' => $user,
            'topStudents' => $topStudentsWithLevels,
        ]);
    }
}
