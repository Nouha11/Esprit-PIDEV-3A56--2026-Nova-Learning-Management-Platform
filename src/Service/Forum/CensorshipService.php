<?php

namespace App\Service\Forum; 

class CensorshipService
{
private array $badWords = [
    'bad', 
    'spam', 
    'idiot', 
    'scam', 
    'fake', 
    'stupid', 
    'merde',
    'loser',  
    'ugly',
    
];
    public function purify(string $text): string
    {
        foreach ($this->badWords as $word) {
            // 1. Find the word (case-insensitive)
            // \b ensures we don't block "scunthorpe" just because it has a bad word inside
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            
            // 2. Create replacement (e.g. "idiot" -> "i****")
            // We keep the first letter so it looks professional
            $len = mb_strlen($word);
            $replacement = mb_substr($word, 0, 1) . str_repeat('*', $len - 1);

            // 3. Replace
            $text = preg_replace($pattern, $replacement, $text);
        }

        return $text;
    }
}