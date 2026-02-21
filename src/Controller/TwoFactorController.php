<?php

namespace App\Controller;

use App\Service\TwoFactorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/2fa')]
#[IsGranted('ROLE_USER')]
class TwoFactorController extends AbstractController
{
    public function __construct(
        private TwoFactorService $twoFactorService
    ) {}

    #[Route('/setup', name: 'app_2fa_setup', methods: ['GET', 'POST'])]
    public function setup(Request $request): Response
    {
        $user = $this->getUser();

        if ($user->isTotpEnabled()) {
            $this->addFlash('warning', '2FA is already enabled for your account.');
            return $this->redirectToRoute('app_2fa_manage');
        }

        if ($request->isMethod('POST')) {
            $code = $request->request->get('code');
            
            if ($this->twoFactorService->verifyAndEnable($user, $code)) {
                $this->addFlash('success', '2FA has been successfully enabled!');
                return $this->redirectToRoute('app_2fa_manage');
            }
            
            $this->addFlash('error', 'Invalid verification code. Please try again.');
        }

        // Generate secret if not exists
        if (!$user->getTotpSecret()) {
            $this->twoFactorService->enableTwoFactor($user);
        }

        $qrCodeDataUri = $this->twoFactorService->generateQrCode($user);

        return $this->render('security/2fa/setup.html.twig', [
            'qrCodeDataUri' => $qrCodeDataUri,
            'secret' => $user->getTotpSecret(),
        ]);
    }

    #[Route('/manage', name: 'app_2fa_manage', methods: ['GET'])]
    public function manage(): Response
    {
        $user = $this->getUser();

        return $this->render('security/2fa/manage.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/disable', name: 'app_2fa_disable', methods: ['POST'])]
    public function disable(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user->isTotpEnabled()) {
            $this->addFlash('warning', '2FA is not enabled for your account.');
            return $this->redirectToRoute('app_2fa_manage');
        }

        $code = $request->request->get('code');
        
        if ($this->twoFactorService->verifyCode($user, $code)) {
            $this->twoFactorService->disableTwoFactor($user);
            $this->addFlash('success', '2FA has been disabled.');
        } else {
            $this->addFlash('error', 'Invalid verification code. 2FA was not disabled.');
        }

        return $this->redirectToRoute('app_2fa_manage');
    }
}
