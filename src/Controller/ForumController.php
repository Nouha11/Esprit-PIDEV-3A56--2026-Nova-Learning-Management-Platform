<?php

namespace App\Controller;

use App\Repository\UserRepository;
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
}