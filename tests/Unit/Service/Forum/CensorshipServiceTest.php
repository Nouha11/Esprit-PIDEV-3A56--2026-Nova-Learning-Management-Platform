<?php

namespace App\Tests\Unit\Service\Forum;

use App\Service\Forum\CensorshipService;
use PHPUnit\Framework\TestCase;

class CensorshipServiceTest extends TestCase
{
    private CensorshipService $censorshipService;

    protected function setUp(): void
    {
        $this->censorshipService = new CensorshipService();
    }

    public function testPurifyLeavesCleanTextUnchanged(): void
    {
        $text = "Hello everyone, this is a very nice and clean message.";
        $result = $this->censorshipService->purify($text);
        
        $this->assertEquals($text, $result);
    }

    public function testPurifyCensorsBadWords(): void
    {
        $text = "This is a stupid scam!";
        $result = $this->censorshipService->purify($text);        
        $this->assertEquals("This is a s***** s***!", $result);
    }

    public function testPurifyIsCaseInsensitive(): void
    {
        $text = "You are an IDIOT and this is FAKE.";
        $result = $this->censorshipService->purify($text);        
        $this->assertEquals("You are an i**** and this is f***.", $result);
    }

    public function testPurifyRespectsWordBoundaries(): void
    {
        $text = "The scamp ran away from the scam.";
        $result = $this->censorshipService->purify($text);        
        $this->assertEquals("The scamp ran away from the s***.", $result);
    }
}