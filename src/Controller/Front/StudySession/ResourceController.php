<?php

namespace App\Controller\Front\StudySession;

use App\Entity\StudySession\Resource;
use App\Entity\StudySession\StudySession;
use App\Form\StudySession\ResourceUploadType;
use App\Service\StudySession\ResourceManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/study-session/{studySessionId}/resource')]
#[IsGranted('ROLE_STUDENT')]
class ResourceController extends AbstractController
{
    public function __construct(
        private ResourceManager $resourceManager,
        private EntityManagerInterface $entityManager,
        private CsrfTokenManagerInterface $csrfTokenManager
    ) {}

    #[Route('/list', name: 'resource_list', methods: ['GET'])]
    public function index(int $studySessionId): Response
    {
        $studySession = $this->entityManager->getRepository(StudySession::class)->find($studySessionId);
        
        if (!$studySession) {
            throw $this->createNotFoundException('Study session not found.');
        }

        // Ensure user can only view resources from their own sessions
        if ($studySession->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot view resources for this study session.');
        }

        return $this->render('front/study_session/resources.html.twig', [
            'study_session' => $studySession,
            'resources' => $studySession->getResources(),
        ]);
    }

    #[Route('/upload', name: 'resource_upload', methods: ['GET', 'POST'])]
    public function upload(int $studySessionId, Request $request): Response
    {
        $studySession = $this->entityManager->getRepository(StudySession::class)->find($studySessionId);
        
        if (!$studySession) {
            throw $this->createNotFoundException('Study session not found.');
        }

        // Ensure user can only upload resources to their own sessions
        if ($studySession->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot upload resources to this study session.');
        }

        $form = $this->createForm(ResourceUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $uploadedFile = $form->get('file')->getData();
                
                if ($uploadedFile) {
                    // Upload PDF via ResourceManager
                    $resource = $this->resourceManager->uploadPDF($uploadedFile, $studySession);
                    
                    $this->addFlash('success', 'PDF resource uploaded successfully.');
                    return $this->redirectToRoute('study_session_index');
                }
                
                $this->addFlash('error', 'No file was uploaded.');
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', 'Invalid file: ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to upload resource: ' . $e->getMessage());
            }
        }

        return $this->render('front/study_session/resource_upload.html.twig', [
            'form' => $form->createView(),
            'study_session' => $studySession,
        ]);
    }

    #[Route('/{id}/download', name: 'resource_download', methods: ['GET'])]
    public function download(int $studySessionId, int $id): Response
    {
        $resource = $this->entityManager->getRepository(Resource::class)->find($id);
        
        if (!$resource) {
            throw $this->createNotFoundException('Resource not found.');
        }

        // Ensure the resource belongs to the specified study session
        if ($resource->getStudySession()->getId() !== $studySessionId) {
            throw $this->createNotFoundException('Resource does not belong to this study session.');
        }

        // Ensure user can only download resources from their own sessions
        if ($resource->getStudySession()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot download this resource.');
        }

        $filePath = $this->resourceManager->getStoragePath($resource);
        
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('File not found on disk.');
        }

        // Serve the PDF with correct headers
        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $resource->getFilename()
        );

        return $response;
    }

    #[Route('/{id}/delete', name: 'resource_delete', methods: ['POST'])]
    public function delete(int $studySessionId, int $id, Request $request): Response
    {
        $resource = $this->entityManager->getRepository(Resource::class)->find($id);
        
        if (!$resource) {
            throw $this->createNotFoundException('Resource not found.');
        }

        // Ensure the resource belongs to the specified study session
        if ($resource->getStudySession()->getId() !== $studySessionId) {
            throw $this->createNotFoundException('Resource does not belong to this study session.');
        }

        // Ensure user can only delete resources from their own sessions
        if ($resource->getStudySession()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot delete this resource.');
        }

        // CSRF validation
        $token = $request->request->get('_token');
        if (!$this->csrfTokenManager->isTokenValid(
            new \Symfony\Component\Security\Csrf\CsrfToken('delete_resource_' . $resource->getId(), $token)
        )) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('study_session_index');
        }

        try {
            // Remove PDF via ResourceManager (handles both file and entity deletion)
            $this->resourceManager->deletePDF($resource);
            
            $this->addFlash('success', 'Resource deleted successfully.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to delete resource: ' . $e->getMessage());
        }

        return $this->redirectToRoute('study_session_index');
    }
}
