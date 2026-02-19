<?php

namespace App\Twig;

use App\Service\TranslationService;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TranslationExtension extends AbstractExtension
{
    private TranslationService $translationService;
    private RequestStack $requestStack;

    public function __construct(TranslationService $translationService, RequestStack $requestStack)
    {
        $this->translationService = $translationService;
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('translate_text', [$this, 'translateText']),
            new TwigFunction('current_locale', [$this, 'getCurrentLocale']),
            new TwigFunction('is_french', [$this, 'isFrench']),
        ];
    }

    public function translateText(string $text): string
    {
        $locale = $this->getCurrentLocale();
        
        if ($locale === 'fr') {
            return $this->translationService->translateText($text, 'fr');
        }
        
        return $text;
    }

    public function getCurrentLocale(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        
        if ($request) {
            return $request->getSession()->get('_locale', 'en');
        }
        
        return 'en';
    }

    public function isFrench(): bool
    {
        return $this->getCurrentLocale() === 'fr';
    }
}