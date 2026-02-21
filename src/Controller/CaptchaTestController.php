<?php

namespace App\Controller;

use App\Service\CaptchaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CaptchaTestController extends AbstractController
{
    #[Route('/captcha/test', name: 'app_captcha_test')]
    public function test(Request $request, CaptchaService $captchaService): Response
    {
        if ($request->isMethod('POST')) {
            $userAnswer = $request->request->get('captcha_answer');
            $captchaType = $request->request->get('captcha_type', 'question');
            
            if ($captchaService->verifyCaptcha($userAnswer)) {
                $this->addFlash('success', '✅ CAPTCHA verified successfully! You are human!');
                $captchaService->clearCaptcha();
            } else {
                $this->addFlash('error', '❌ Incorrect CAPTCHA answer. Please try again.');
            }
            
            return $this->redirectToRoute('app_captcha_test', ['type' => $captchaType]);
        }
        
        $captchaType = $request->query->get('type', 'question');
        $captcha = null;
        
        if ($captchaType === 'question') {
            $captcha = $captchaService->generateCaptcha();
        } else {
            $captcha = $captchaService->generateVisualCaptcha();
        }
        
        return $this->render('captcha/test.html.twig', [
            'captchaType' => $captchaType,
            'captchaQuestion' => $captcha['question'] ?? null,
        ]);
    }
}
