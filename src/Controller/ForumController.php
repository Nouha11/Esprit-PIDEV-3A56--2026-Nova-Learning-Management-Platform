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
use Symfony\Component\HttpFoundation\JsonResponse;

class ForumController extends AbstractController
{
    #[Route('/forum', name: 'app_forum')]
    public function index(PostRepository $postRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            
            if (!$user) {
                $this->addFlash('error', 'You must be logged in to create a post.');
                return $this->redirectToRoute('app_login');
            }
            
            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setUpvotes(0);
            $post->setAuthor($user);
            $post->setIsLocked(false);

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Post created successfully!');
            return $this->redirectToRoute('app_forum');
        }

        $searchQuery = $request->query->get('q');

        if ($searchQuery) {
            $posts = $postRepository->adminSearch($searchQuery);
        } else {
            $posts = $postRepository->findBy([], ['createdAt' => 'DESC']);
        }

        return $this->render('forum/index.html.twig', [
            'form' => $form->createView(),
            'posts' => $posts,
            'searchQuery' => $searchQuery,
        ]);
    }

    #[Route('/forum/{id}', name: 'app_forum_show')]
    public function show(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // SECURITY GUARD
            if ($post->isLocked()) {
                $this->addFlash('error', 'This discussion is locked. You cannot add new replies.');
                return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()]);
            }

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

        /** @var \App\Entity\User $user */
        // FIXED: Use getRoles() array check instead of getRole()
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $isAuthor = $post->getAuthor()->getId() === $user->getId();

        // If user is NOT author AND NOT admin, deny access
        if (!$isAuthor && !$isAdmin) {
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

        /** @var \App\Entity\User $user */
        // FIXED: Use getRoles() array check
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $isAuthor = $post->getAuthor()->getId() === $user->getId();

        if (!$isAuthor && !$isAdmin) {
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
#[Route('/forum/post/{id}/upvote', name: 'app_forum_upvote', methods: ['POST'])]
public function upvote(Post $post, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();

    // 1. Check if user is logged in
    if (!$user) {
        return $this->json(['error' => 'You must be logged in to upvote.'], 403);
    }

    // 2. Toggle the upvote logic
    if ($post->isUpvotedBy($user)) {
        $post->removeUpvoter($user);
        $isUpvoted = false;
    } else {
        $post->addUpvoter($user);
        $isUpvoted = true;
    }

    // 3. Save to database
    $entityManager->flush();

    // 4. Return JSON for the AJAX call
    return $this->json([
        'upvotes' => $post->getUpvotes(),
        'isUpvoted' => $isUpvoted
    ]);
}

}