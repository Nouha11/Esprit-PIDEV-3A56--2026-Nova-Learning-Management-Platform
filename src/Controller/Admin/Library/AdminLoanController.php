<?php

namespace App\Controller\Admin\Library;

use App\Entity\Library\Loan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/loans')]
#[IsGranted('ROLE_ADMIN')]
class AdminLoanController extends AbstractController
{
    #[Route('/', name: 'admin_loans_index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // Debug: Test if controller is reached
        $loans = $em->getRepository(Loan::class)->findAll();
        
        if (empty($loans)) {
            return new Response('No loans found in database. Total count: ' . count($loans));
        }
        
        // Filtrer par statut
        $status = $request->query->get('status', 'all');
        
        try {
            $qb = $em->getRepository(Loan::class)->createQueryBuilder('l')
                ->leftJoin('l.book', 'b')
                ->leftJoin('l.user', 'u')
                ->leftJoin('l.library', 'lib')
                ->addSelect('b', 'u', 'lib')
                ->orderBy('l.requestedAt', 'DESC');

            if ($status !== 'all') {
                $qb->where('l.status = :status')
                   ->setParameter('status', $status);
            }

            $loans = $qb->getQuery()->getResult();

            // Compter les emprunts par statut
            $stats = [
                'pending' => $em->getRepository(Loan::class)->count(['status' => Loan::STATUS_PENDING]),
                'approved' => $em->getRepository(Loan::class)->count(['status' => Loan::STATUS_APPROVED]),
                'active' => $em->getRepository(Loan::class)->count(['status' => Loan::STATUS_ACTIVE]),
                'returned' => $em->getRepository(Loan::class)->count(['status' => Loan::STATUS_RETURNED]),
                'rejected' => $em->getRepository(Loan::class)->count(['status' => Loan::STATUS_REJECTED]),
            ];
        } catch (\Exception $e) {
            // Debug: show error
            return new Response('Error: ' . $e->getMessage());
        }

        return $this->render('admin/loan/index.html.twig', [
            'loans' => $loans,
            'currentStatus' => $status,
            'stats' => $stats,
        ]);
    }

    #[Route('/{id}', name: 'admin_loans_show')]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $loan = $em->getRepository(Loan::class)->find($id);
        
        if (!$loan) {
            throw $this->createNotFoundException('Loan not found');
        }

        return $this->render('admin/loan/show.html.twig', [
            'loan' => $loan,
        ]);
    }

    #[Route('/{id}/approve', name: 'admin_loans_approve', methods: ['POST'])]
    public function approve(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $loan = $em->getRepository(Loan::class)->find($id);
        
        if (!$loan) {
            throw $this->createNotFoundException('Loan not found');
        }

        if ($this->isCsrfTokenValid('approve' . $loan->getId(), $request->request->get('_token'))) {
            // Vérifier les règles métier avant d'approuver
            $user = $loan->getUser();
            
            // Règle 1: Maximum 3 emprunts actifs par utilisateur
            $activeLoans = $em->getRepository(Loan::class)->count([
                'user' => $user,
                'status' => Loan::STATUS_ACTIVE
            ]);
            
            if ($activeLoans >= 3) {
                $this->addFlash('error', 'User already has 3 active loans. Cannot approve more.');
                return $this->redirectToRoute('admin_loans_show', ['id' => $id]);
            }

            // Règle 2: Vérifier si l'utilisateur a des emprunts en retard
            $overdueLoans = $em->getRepository(Loan::class)->createQueryBuilder('l')
                ->where('l.user = :user')
                ->andWhere('l.status = :status')
                ->setParameter('user', $user)
                ->setParameter('status', Loan::STATUS_OVERDUE)
                ->getQuery()
                ->getResult();

            if (count($overdueLoans) > 0) {
                $this->addFlash('warning', 'Warning: User has ' . count($overdueLoans) . ' overdue loan(s).');
            }

            $loan->setStatus(Loan::STATUS_APPROVED);
            $loan->setApprovedAt(new \DateTimeImmutable());
            $em->flush();

            $this->addFlash('success', 'Loan approved successfully!');
        }

        return $this->redirectToRoute('admin_loans_index');
    }

    #[Route('/{id}/reject', name: 'admin_loans_reject', methods: ['POST'])]
    public function reject(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $loan = $em->getRepository(Loan::class)->find($id);
        
        if (!$loan) {
            throw $this->createNotFoundException('Loan not found');
        }

        if ($this->isCsrfTokenValid('reject' . $loan->getId(), $request->request->get('_token'))) {
            $reason = $request->request->get('reason', 'No reason provided');
            
            $loan->setStatus(Loan::STATUS_REJECTED);
            $loan->setRejectionReason($reason);
            $em->flush();

            $this->addFlash('success', 'Loan rejected.');
        }

        return $this->redirectToRoute('admin_loans_index');
    }

    #[Route('/{id}/mark-active', name: 'admin_loans_mark_active', methods: ['POST'])]
    public function markActive(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $loan = $em->getRepository(Loan::class)->find($id);
        
        if (!$loan) {
            throw $this->createNotFoundException('Loan not found');
        }

        if ($this->isCsrfTokenValid('mark_active' . $loan->getId(), $request->request->get('_token'))) {
            if ($loan->getStatus() !== Loan::STATUS_APPROVED) {
                $this->addFlash('error', 'Only approved loans can be marked as active.');
                return $this->redirectToRoute('admin_loans_show', ['id' => $id]);
            }

            $loan->setStatus(Loan::STATUS_ACTIVE);
            $em->flush();

            $this->addFlash('success', 'Loan marked as active (book picked up).');
        }

        return $this->redirectToRoute('admin_loans_index');
    }

    #[Route('/{id}/mark-returned', name: 'admin_loans_mark_returned', methods: ['POST'])]
    public function markReturned(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $loan = $em->getRepository(Loan::class)->find($id);
        
        if (!$loan) {
            throw $this->createNotFoundException('Loan not found');
        }

        if ($this->isCsrfTokenValid('mark_returned' . $loan->getId(), $request->request->get('_token'))) {
            $loan->setStatus(Loan::STATUS_RETURNED);
            $loan->setEndAt(new \DateTimeImmutable()); // Enregistrer la date de retour réelle
            $em->flush();

            $this->addFlash('success', 'Loan marked as returned.');
        }

        return $this->redirectToRoute('admin_loans_index');
    }
}
