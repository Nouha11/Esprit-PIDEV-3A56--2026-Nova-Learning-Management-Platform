<?php

namespace App\Twig;

use App\Repository\Quiz\QuizReportRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class QuizReportExtension extends AbstractExtension
{
    public function __construct(
        private QuizReportRepository $reportRepository
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pending_reports_count', [$this, 'getPendingReportsCount']),
        ];
    }

    public function getPendingReportsCount(): int
    {
        return $this->reportRepository->countPendingReports();
    }
}
