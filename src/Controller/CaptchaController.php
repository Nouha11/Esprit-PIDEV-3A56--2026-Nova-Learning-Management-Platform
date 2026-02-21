<?php

namespace App\Controller;

use App\Service\CaptchaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CaptchaController extends AbstractController
{
    public function __construct(
        private CaptchaService $captchaService
    ) {}

    #[Route('/captcha/generate', name: 'app_captcha_generate')]
    public function generate(): Response
    {
        $captcha = $this->captchaService->generateVisualCaptcha();
        
        return new Response($captcha['svg'], 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    #[Route('/captcha/refresh', name: 'app_captcha_refresh')]
    public function refresh(): Response
    {
        $this->captchaService->clearCaptcha();
        $captcha = $this->captchaService->generateVisualCaptcha();
        
        return $this->json([
            'svg' => $captcha['svg'],
            'success' => true,
        ]);
    }
}
