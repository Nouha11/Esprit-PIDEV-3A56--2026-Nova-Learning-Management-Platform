<?php
namespace App\Controller\Front\Game;

use App\Entity\Gamification\Reward;
use App\Repository\Gamification\RewardRepository;
use App\Service\game\RewardService;
use App\Service\game\CertificateService;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/rewards')]
class RewardController extends AbstractController
{
    public function __construct(
        private RewardService $rewardService,
        private CertificateService $certificateService,
        private PaginatorInterface $paginator,
        private RewardRepository $rewardRepository
    ) {
    }

    /**
    * View my earned rewards (placeholder - rewards tracking removed)
    */
    #[Route('/my-rewards', name: 'front_reward_my_rewards', methods: ['GET'])]
    public function myRewards(): Response
    {
        $user = $this->getUser();
        $student = $user->getStudentProfile();

        if (!$student) {
            $this->addFlash('error', 'Student profile not found');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('front/game/my_rewards.html.twig', [
            'student' => $student,
        ]);
    }

    /**
    * View all available rewards (gallery) with pagination
    */
    #[Route('/browse', name: 'front_reward_browse', methods: ['GET'])]
    public function browse(Request $request): Response
    {
        $user = $this->getUser();
        $student = $user ? $user->getStudentProfile() : null;

        $query = $this->rewardRepository->createQueryBuilder('r')
            ->where('r.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('r.id', 'DESC')
            ->getQuery();

        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            8 // 8 rewards per page
        );

        return $this->render('front/game/browse.html.twig', [
            'rewards' => $pagination,
            'student' => $student,
        ]);
    }

    /**
     * View reward details and associated games with QR code
     */
    #[Route('/{id}', name: 'front_reward_show', methods: ['GET'])]
    public function show(Reward $reward): Response
    {
        $user = $this->getUser();
        $student = $user ? $user->getStudentProfile() : null;

        // Generate QR code for this reward
        $rewardUrl = $this->generateUrl('front_reward_show', ['id' => $reward->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $qrCode = new QrCode(
            data: $rewardUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        $qrCodeDataUri = $result->getDataUri();

        return $this->render('front/game/reward_show.html.twig', [
            'reward' => $reward,
            'games' => $reward->getGames(),
            'student' => $student,
            'qrCode' => $qrCodeDataUri,
        ]);
    }

    /**
     * Download PDF certificate for an earned reward
     */
    #[Route('/{id}/certificate', name: 'front_reward_certificate', methods: ['GET'])]
    #[IsGranted('ROLE_STUDENT')]
    public function downloadCertificate(Reward $reward): Response
    {
        $user = $this->getUser();
        $student = $user->getStudentProfile();

        if (!$student) {
            $this->addFlash('error', 'Student profile not found');
            return $this->redirectToRoute('app_home');
        }

        // Check if student has earned this reward
        if (!$student->hasEarnedReward($reward)) {
            $this->addFlash('error', 'You have not earned this reward yet');
            return $this->redirectToRoute('front_reward_my_rewards');
        }

        // Only generate certificates for Achievement rewards
        if ($reward->getType() !== 'ACHIEVEMENT') {
            $this->addFlash('error', 'Certificates are only available for Achievement rewards');
            return $this->redirectToRoute('front_reward_my_rewards');
        }

        // Use current date as earned date (in a real app, you'd track this in the database)
        $earnedDate = new \DateTime();

        // Generate and return the PDF certificate
        return $this->certificateService->generateCertificate($student, $reward, $earnedDate);
    }
}
