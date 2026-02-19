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
        
        // Count by role using DQL to avoid issues
        $adminCount = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.role = :role')
            ->setParameter('role', 'ROLE_ADMIN')
            ->getQuery()
            ->getSingleScalarResult();
            
        $studentCount = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.role = :role')
            ->setParameter('role', 'ROLE_STUDENT')
            ->getQuery()
            ->getSingleScalarResult();
            
        $tutorCount = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.role = :role')
            ->setParameter('role', 'ROLE_TUTOR')
            ->getQuery()
            ->getSingleScalarResult();
        
        // Get recent users with proper entity hydration
        $recentUsers = $userRepository->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

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
