<?php

namespace App\Controller\Front\Library;

use App\Entity\Library\DigitalPurchase;
use App\Entity\Library\Loan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur pour la bibliothèque personnelle de l'utilisateur
 */
#[Route('/my-library')]
class MyLibraryController extends AbstractController
{
    #[Route('/', name: 'my_library')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        // Récupérer les livres numériques achetés
        $digitalPurchases = $em->getRepository(DigitalPurchase::class)->findBy(
            ['user' => $user],
            ['purchasedAt' => 'DESC']
        );
        
        // Récupérer les emprunts actifs (approuvés ou actifs, non retournés)
        $activeLoans = $em->getRepository(Loan::class)->createQueryBuilder('l')
            ->where('l.user = :user')
            ->andWhere('l.status IN (:statuses)')
            ->setParameter('user', $user)
            ->setParameter('statuses', [Loan::STATUS_PENDING, Loan::STATUS_APPROVED, Loan::STATUS_ACTIVE])
            ->orderBy('l.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();
        
        // Récupérer l'historique des emprunts (retournés ou rejetés)
        $loanHistory = $em->getRepository(Loan::class)->createQueryBuilder('l')
            ->where('l.user = :user')
            ->andWhere('l.status IN (:statuses)')
            ->setParameter('user', $user)
            ->setParameter('statuses', [Loan::STATUS_RETURNED, Loan::STATUS_REJECTED])
            ->orderBy('l.endAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
        
        return $this->render('front/library/my_library.html.twig', [
            'digitalPurchases' => $digitalPurchases,
            'activeLoans' => $activeLoans,
            'loanHistory' => $loanHistory,
        ]);
    }
}
