<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LocaleController extends AbstractController
{
    #[Route('/change-locale/{locale}', name: 'app_change_locale', requirements: ['locale' => 'en|fr'])]
    public function changeLocale(string $locale, Request $request): Response
    {
        // Store the locale in the session
        $request->getSession()->set('_locale', $locale);
        
        // Get the referer URL to redirect back
        $referer = $request->headers->get('referer');
        
        // If no referer or referer is from external site, redirect to home
        if (!$referer || !str_contains($referer, $request->getSchemeAndHttpHost())) {
            return $this->redirectToRoute('app_home');
        }
        
        // Redirect back to the previous page
        return $this->redirect($referer);
    }
}
