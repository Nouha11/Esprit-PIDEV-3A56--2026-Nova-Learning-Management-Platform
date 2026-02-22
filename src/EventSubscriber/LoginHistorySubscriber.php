<?php

namespace App\EventSubscriber;

use App\Entity\users\User;
use App\Service\LoginHistoryService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class LoginHistorySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoginHistoryService $loginHistoryService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        
        if ($user instanceof User) {
            // Check if 2FA was used
            $is2faUsed = $user->isTotpAuthenticationEnabled();
            
            $this->loginHistoryService->logLoginAttempt(
                $user,
                'success',
                null,
                $is2faUsed
            );
        }
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $exception = $event->getException();
        $passport = $event->getPassport();
        
        // Try to get the user from the passport
        $user = null;
        if ($passport && method_exists($passport, 'getUser')) {
            $user = $passport->getUser();
        }

        // If we have a user, log the failed attempt
        if ($user instanceof User) {
            $failureReason = $exception->getMessage();
            
            $this->loginHistoryService->logLoginAttempt(
                $user,
                'failed',
                $failureReason
            );
        }
    }
}
