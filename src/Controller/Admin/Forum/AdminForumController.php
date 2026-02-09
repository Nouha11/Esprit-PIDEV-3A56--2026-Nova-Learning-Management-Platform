<?php

namespace App\Controller\Admin\Forum;

use App\Entity\Forum\Post;
use App\Repository\Forum\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/forum')]
class AdminForumController extends AbstractController
{
    // LIST ALL POSTS
    #[Route('/', name: 'app_admin_forum_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('admin/forum/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    // DELETE POST (Admin Power)
    #[Route('/{id}/delete', name: 'app_admin_forum_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
            $this->addFlash('success', 'Discussion deleted by Moderator.');
        }

        return $this->redirectToRoute('app_admin_forum_index');
    }
}