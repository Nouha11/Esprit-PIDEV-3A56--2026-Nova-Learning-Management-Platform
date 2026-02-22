<?php

namespace App\Controller\Front\StudySession;

use App\Entity\StudySession\Course;
use App\Entity\StudySession\EnrollmentRequest;
use App\Service\StudySession\EnrollmentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/enrollment')]
class EnrollmentController extends AbstractController
{
    public function __construct(
        private EnrollmentService $enrollmentService
    ) {}

    /**
     * Student requests enrollment in a course
     */
    #[Route('/request/{id}', name: 'enrollment_request', methods: ['POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function request(Course $course, Request $request): Response
    {
        try {
            $message = $request->request->get('message');
            $this->enrollmentService->requestEnrollment($this->getUser(), $course, $message);
            $this->addFlash('success', 'Enrollment request sent successfully. You will be notified when the tutor responds.');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('course_index');
    }

    /**
     * Tutor views pending enrollment requests
     */
    #[Route('/requests', name: 'enrollment_requests', methods: ['GET'])]
    #[IsGranted('ROLE_TUTOR')]
    public function viewRequests(): Response
    {
        $requests = $this->enrollmentService->getPendingRequestsForTutor($this->getUser());

        return $this->render('front/enrollment/requests.html.twig', [
            'requests' => $requests
        ]);
    }

    /**
     * Tutor approves enrollment request
     */
    #[Route('/approve/{id}', name: 'enrollment_approve', methods: ['POST'])]
    #[IsGranted('ROLE_TUTOR')]
    public function approve(EnrollmentRequest $request): Response
    {
        try {
            // Verify the tutor owns the course
            if ($request->getCourse()->getCreatedBy() !== $this->getUser()) {
                throw new \RuntimeException('You can only approve requests for your own courses');
            }

            $this->enrollmentService->approveEnrollment($request, $this->getUser());
            $this->addFlash('success', 'Enrollment request approved successfully');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('enrollment_requests');
    }

    /**
     * Tutor rejects enrollment request
     */
    #[Route('/reject/{id}', name: 'enrollment_reject', methods: ['POST'])]
    #[IsGranted('ROLE_TUTOR')]
    public function reject(EnrollmentRequest $request): Response
    {
        try {
            // Verify the tutor owns the course
            if ($request->getCourse()->getCreatedBy() !== $this->getUser()) {
                throw new \RuntimeException('You can only reject requests for your own courses');
            }

            $this->enrollmentService->rejectEnrollment($request, $this->getUser());
            $this->addFlash('success', 'Enrollment request rejected');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('enrollment_requests');
    }
}
