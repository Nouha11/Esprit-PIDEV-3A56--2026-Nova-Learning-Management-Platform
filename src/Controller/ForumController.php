<?php

namespace App\Controller;

use App\Repository\UserRepository;
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
    public function index(PostRepository $postRepository, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // 1. AUTO-SETUP: Set the hidden fields
            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setUpvotes(0);
            
            // 2. ASSIGN AUTHOR: Grab the "Test User" (ID 1) we created
            // Later, when you have login, this will be: $this->getUser()
            $author = $userRepository->find(1); 
            $post->setAuthor($author);

            // 3. SAVE
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_forum');
        }

        return $this->render('forum/index.html.twig', [
            'form' => $form->createView(),
            'posts' => $postRepository->findAll(),
        ]);
    }
    #[Route('/forum/{id}', name: 'app_forum_show')]
    public function show(Post $post, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        // 1. Create the Comment Form
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        // 2. Handle the Submit
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setIsSolution(false);
            $comment->setPost($post); // Link to THIS post
            
            // Temporary: Use User ID 1 again (until login is ready)
            // make sure your User repository is correct!
            $author = $userRepository->find(1); 
            $comment->setAuthor($author);

            $entityManager->persist($comment);
            $entityManager->flush();

            // Refresh the page to show the new comment
            return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()]);
        }
        

        // 3. Render the page
        return $this->render('forum/show.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/forum/delete/{id}', name: 'app_forum_delete')]
    public function delete(Post $post, EntityManagerInterface $entityManager): Response
    {
        // 1. Remove the post
        $entityManager->remove($post);
        $entityManager->flush();

        // 2. Go back to the list
        return $this->redirectToRoute('app_forum');
    }
    #[Route('/forum/edit/{id}', name: 'app_forum_edit')]
    public function edit(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. Create the form using the EXISTING post
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        // 2. Handle the save
        if ($form->isSubmitted() && $form->isValid()) {
            // We don't need persist(), just flush() because the post is already in DB
            $entityManager->flush();

            // Redirect back to the post
            return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()]);
        }

        // 3. Render the edit page
        return $this->render('forum/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}