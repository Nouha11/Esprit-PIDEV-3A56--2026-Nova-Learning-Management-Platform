<?php

namespace App\EventSubscriber;

use App\Service\CaptchaService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class LoginCaptchaSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CaptchaService $captchaService,
        private RequestStack $requestStack,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', 1000],
        ];
    }

    public function onCheckPassport(CheckPassportEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        
        if (!$request) {
            return;
        }

        // Only check CAPTCHA for login form submissions
        if ($request->attributes->get('_route') !== 'app_login') {
            return;
        }

        $captchaAnswer = $request->request->get('captcha_answer');
        
        if (!$this->captchaService->verifyCaptcha($captchaAnswer)) {
            // Generate new CAPTCHA for next attempt
            $this->captchaService->generateCaptcha();
            
            throw new CustomUserMessageAuthenticationException(
                'Invalid CAPTCHA answer. Please try again.'
            );
        }
    }
}
