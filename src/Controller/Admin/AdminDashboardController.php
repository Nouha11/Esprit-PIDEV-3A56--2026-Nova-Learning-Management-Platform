<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use App\Repository\StudentProfileRepository;
use App\Repository\TutorProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard')]
    public function index(
        UserRepository $userRepository,
        StudentProfileRepository $studentRepository,
        TutorProfileRepository $tutorRepository
    ): Response {
        // Get statistics
        $totalUsers = count($userRepository->findAll());
        $totalStudents = count($studentRepository->findAll());
        $totalTutors = count($tutorRepository->findAll());
        
        // Count by role
        $adminCount = count($userRepository->findBy(['role' => 'ROLE_ADMIN']));
        $studentCount = count($userRepository->findBy(['role' => 'ROLE_STUDENT']));
        $tutorCount = count($userRepository->findBy(['role' => 'ROLE_TUTOR']));
        
        // Get recent users
        $recentUsers = $userRepository->findBy([], ['id' => 'DESC'], 5);

        return $this->render('admin/dashboard/index.html.twig', [
            'totalUsers' => $totalUsers,
            'totalStudents' => $totalStudents,
            'totalTutors' => $totalTutors,
            'adminCount' => $adminCount,
            'studentCount' => $studentCount,
            'tutorCount' => $tutorCount,
            'recentUsers' => $recentUsers,
        ]);
    }
}
