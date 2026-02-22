<?php

namespace App\Controller\Admin;

use App\Service\LoginHistoryService;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/login-history')]
#[IsGranted('ROLE_ADMIN')]
class LoginHistoryController extends AbstractController
{
    public function __construct(
        private LoginHistoryService $loginHistoryService,
        private UserRepository $userRepository
    ) {}

    #[Route('', name: 'admin_login_history_index')]
    public function index(Request $request): Response
    {
        $limit = 50;
        $page = max(1, $request->query->getInt('page', 1));
        $offset = ($page - 1) * $limit;

        $loginHistory = $this->loginHistoryService->getAllLoginHistory($limit, $offset);

        return $this->render('admin/login_history/index.html.twig', [
            'loginHistory' => $loginHistory,
            'currentPage' => $page,
        ]);
    }

    #[Route('/user/{id}', name: 'admin_login_history_user')]
    public function userHistory(int $id): Response
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $loginHistory = $this->loginHistoryService->getRecentLogins($user, 50);
        $statistics = $this->loginHistoryService->getLoginStatistics($user, 30);
        $suspiciousActivity = $this->loginHistoryService->detectSuspiciousActivity($user);

        return $this->render('admin/login_history/user.html.twig', [
            'user' => $user,
            'loginHistory' => $loginHistory,
            'statistics' => $statistics,
            'suspiciousActivity' => $suspiciousActivity,
        ]);
    }
}
