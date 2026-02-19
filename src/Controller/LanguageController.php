<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LanguageController extends AbstractController
{
    #[Route('/language/{locale}', name: 'app_change_language', requirements: ['locale' => 'en|fr'])]
    public function changeLanguage(string $locale, Request $request): Response
    {
        // Store the language preference in session
        $request->getSession()->set('_locale', $locale);
        
        // Redirect back to the previous page or home
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
        
        return $this->redirectToRoute('app_home');
    }
}