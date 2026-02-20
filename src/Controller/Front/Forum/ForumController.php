<?php

namespace App\Controller\Front\Forum;

use App\Entity\Forum\Comment;
use App\Form\CommentType;
use App\Entity\Forum\Post;
use App\Form\PostType;
use App\Repository\Forum\PostRepository;
use App\Entity\users\User;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\Forum\CensorshipService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ForumController extends AbstractController
{
    #[Route('/forum', name: 'app_forum')]
    public function index(
        PostRepository $postRepository, 
        Request $request, 
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator,
        CensorshipService $censorship
    ): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            
            if (!$user) {
                $this->addFlash('error', 'You must be logged in to create a post.');
                return $this->redirectToRoute('app_login');
            }

            // Clean bad words
            $post->setTitle($censorship->purify($post->getTitle()));
            $post->setContent($censorship->purify($post->getContent()));
            
            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setUpvotes(0);
            $post->setAuthor($user);
            $post->setIsLocked(false);

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Post created successfully!');
            return $this->redirectToRoute('app_forum');
        }

        // --- FILTERING LOGIC ---
        $searchQuery = $request->query->get('q');
        $filter = $request->query->get('filter'); // Get 'popular' or 'unanswered' from URL

        if ($searchQuery) {
            $query = $postRepository->adminSearch($searchQuery);
        } else {
            // If no search, check for filters
            $query = $postRepository->findByFilter($filter);
        }

        $posts = $paginator->paginate(
            $query, 
            $request->query->getInt('page', 1), 
            5 
        );

        return $this->render('front/forum/index.html.twig', [
            'form' => $form->createView(),
            'posts' => $posts,
            'searchQuery' => $searchQuery,
            'currentFilter' => $filter, // Pass filter to Twig for "Active" class
        ]);
    }

    #[Route('/forum/{id}', name: 'app_forum_show')]
    public function show(
        ?Post $post, // <-- Notice the '?' makes this nullable!
        Request $request, 
        EntityManagerInterface $entityManager,
        CensorshipService $censorship
    ): Response
    {
        // 1. Check if the post was deleted (or never existed)
        if (!$post) {
            // Render our brand new "Deleted" page!
            return $this->render('front/forum/deleted.html.twig');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($post->isLocked()) {
                $this->addFlash('error', 'This discussion is locked. You cannot add new replies.');
                return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()]);
            }

            /** @var User $user */
            $user = $this->getUser();
            
            if (!$user) {
                $this->addFlash('error', 'You must be logged in to comment.');
                return $this->redirectToRoute('app_login');
            }

            // Clean bad words
            $comment->setContent($censorship->purify($comment->getContent()));

            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setIsSolution(false);
            $comment->setPost($post);
            $comment->setAuthor($user);

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Comment added successfully!');
            return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()]);
        }

        return $this->render('front/forum/show.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/forum/delete/{id}', name: 'app_forum_delete', methods: ['POST'])]
    public function delete(Post $post, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'You must be logged in.');
            return $this->redirectToRoute('app_login');
        }

        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $isAuthor = $post->getAuthor()->getId() === $user->getId();

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
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'You must be logged in.');
            return $this->redirectToRoute('app_login');
        }

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

        return $this->render('front/forum/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    #[Route('/forum/post/{id}/upvote', name: 'app_forum_upvote', methods: ['POST'])]
    public function upvote(Post $post, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'You must be logged in to upvote.'], 403);
        }

        if ($post->isUpvotedBy($user)) {
            $post->removeUpvoter($user);
            $isUpvoted = false;
        } else {
            $post->addUpvoter($user);
            $isUpvoted = true;
        }

        $entityManager->flush();

        return $this->json([
            'upvotes' => $post->getUpvotes(),
            'isUpvoted' => $isUpvoted
        ]);
    }

    #[Route('/forum/comment/{id}/solution', name: 'app_forum_solution', methods: ['POST'])]
    public function markAsSolution(Comment $comment, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $post = $comment->getPost();

        $isAuthor = $user && $post->getAuthor()->getId() === $user->getId();
        $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles());

        if (!$isAuthor && !$isAdmin) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        if ($comment->isSolution()) {
            $comment->setIsSolution(false);
        } else {
            foreach ($post->getComments() as $otherComment) {
                $otherComment->setIsSolution(false);
            }
            $comment->setIsSolution(true);
        }

        $entityManager->flush();

        return $this->json([
            'isSolution' => $comment->isSolution()
        ]);
    }

    #[Route('/forum/post/{id}/lock', name: 'app_forum_toggle_lock')]
    public function toggleLock(Post $post, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'You must be logged in.');
            return $this->redirectToRoute('app_login');
        }

        $isAuthor = $post->getAuthor()->getId() === $user->getId();
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());

        if (!$isAuthor && !$isAdmin) {
            $this->addFlash('error', 'You are not authorized to manage this discussion.');
            return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()]);
        }

        $post->setIsLocked(!$post->isLocked());
        $entityManager->flush();

        $status = $post->isLocked() ? 'locked' : 'unlocked';
        $this->addFlash('success', "Discussion has been $status.");

        return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()]);
    }

    #[Route('/forum/comment/{id}/vote/{type}', name: 'app_forum_comment_vote', methods: ['POST'])]
    public function voteComment(Comment $comment, string $type, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Login required'], 403);
        }

        if ($type === 'up') {
            if ($comment->isUpvotedBy($user)) {
                $comment->removeUpvoter($user); 
            } else {
                $comment->addUpvoter($user);    
                $comment->removeDownvoter($user); 
            }
        } elseif ($type === 'down') {
            if ($comment->isDownvotedBy($user)) {
                $comment->removeDownvoter($user); 
            } else {
                $comment->addDownvoter($user);    
                $comment->removeUpvoter($user);   
            }
        }

        $entityManager->flush();

        return $this->json([
            'score' => $comment->getScore(),
            'upvoted' => $comment->isUpvotedBy($user),
            'downvoted' => $comment->isDownvotedBy($user)
        ]);
    }


    #[Route('/post/{id}/report', name: 'app_forum_report', methods: ['POST'])]
    public function report(\App\Entity\Forum\Post $post, \Symfony\Component\HttpFoundation\Request $request, \Doctrine\ORM\EntityManagerInterface $entityManager): \Symfony\Component\HttpFoundation\Response
    {
        $user = $this->getUser();
        
        // 1. Security Check
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to report a post.');
            return $this->redirectToRoute('app_login');
        }

        // 2. Prevent the author from reporting their own post
        if ($user === $post->getAuthor()) {
            $this->addFlash('warning', 'You cannot report your own post.');
            return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()]);
        }

        // 3. Create the Report
        $reason = $request->request->get('reason', 'Inappropriate content');
        
        $report = new \App\Entity\Forum\Report();
        $report->setPost($post);
        $report->setReporter($user); // The person clicking the button
        $report->setReason($reason);
        $report->setCreatedAt(new \DateTimeImmutable());

        // 4. Save to Database
        $entityManager->persist($report);
        $entityManager->flush();

        $this->addFlash('success', 'Thank you. The post has been reported to moderators.');
        return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()]);
    }
}