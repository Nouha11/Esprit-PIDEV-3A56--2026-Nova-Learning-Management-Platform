<?php

namespace App\Controller;

use App\Entity\Forum\Comment;
use App\Form\CommentType;
use App\Entity\Forum\Post;
use App\Form\PostType;
use App\Repository\Forum\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ForumController extends AbstractController
{
    #[Route('/forum', name: 'app_forum')]
    public function index(PostRepository $postRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the currently logged-in user
            $user = $this->getUser();
            
            if (!$user) {
                $this->addFlash('error', 'You must be logged in to create a post.');
                return $this->redirectToRoute('app_login');
            }
            
            // Set the post data
            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setUpvotes(0);
            $post->setAuthor($user);

            // Save
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Post created successfully!');
            return $this->redirectToRoute('app_forum');
        }

        return $this->render('forum/index.html.twig', [
            'form' => $form->createView(),
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/forum/{id}', name: 'app_forum_show')]
    public function show(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the currently logged-in user
            $user = $this->getUser();
            
            if (!$user) {
                $this->addFlash('error', 'You must be logged in to comment.');
                return $this->redirectToRoute('app_login');
            }

            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setIsSolution(false);
            $comment->setPost($post);
            $comment->setAuthor($user);

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Comment added successfully!');
            return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()]);
        }

        return $this->render('forum/show.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/forum/delete/{id}', name: 'app_forum_delete', methods: ['POST'])]
    public function delete(Post $post, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'You must be logged in.');
            return $this->redirectToRoute('app_login');
        }

        // Check if user is the author or admin
        if ($post->getAuthor()->getId() !== $user->getId() && $user->getRole() !== 'ROLE_ADMIN') {
            $this->addFlash('error', 'You cannot delete this post.');
            return $this->redirectToRoute('app_forum');
        }

        $entityManager->remove($post);
        $entityManager->flush();

        $this->addFlash('success', 'Post deleted successfully!');
        return $this->redirectToRoute('app_forum');
    }

    #[Route('/forum/edit/{id}', name: 'app_forum_edit')]
    public function edit(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'You must be logged in.');
            return $this->redirectToRoute('app_login');
        }

        // Check if user is the author or admin
        if ($post->getAuthor()->getId() !== $user->getId() && $user->getRole() !== 'ROLE_ADMIN') {
            $this->addFlash('error', 'You cannot edit this post.');
            return $this->redirectToRoute('app_forum');
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Post updated successfully!');
            return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()]);
        }

        return $this->render('forum/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }
}