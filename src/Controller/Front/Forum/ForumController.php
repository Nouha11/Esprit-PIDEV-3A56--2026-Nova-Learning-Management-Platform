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
use App\Service\Forum\AiSummaryService;
use App\Service\Forum\OpenGraphFetcher;
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
        CensorshipService $censorship,
        OpenGraphFetcher $ogFetcher 
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

            $post->setTitle($censorship->purify($post->getTitle()));
            $post->setContent($censorship->purify($post->getContent()));
            
            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setUpvotes(0);
            $post->setAuthor($user);
            $post->setIsLocked(false);

            if ($post->getLink()) {
                $ogData = $ogFetcher->fetch($post->getLink());
                if ($ogData) {
                    $post->setLinkTitle($ogData['title']);
                    $post->setLinkDescription($ogData['description']);
                    $post->setLinkImage($ogData['image']);
                }
            }

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Post created successfully!');
            return $this->redirectToRoute('app_forum');
        }

        $searchQuery = $request->query->get('q');
        $filter = $request->query->get('filter');
        $spaceId = $request->query->get('space'); 
        $sortBy = $request->query->get('sortBy', 'hot'); // Default to 'hot'

        /** @var User $user */
        $user = $this->getUser();

        // ==========================================
        // --- FIXED: QUERY AND SORTING LOGIC ---
        // ==========================================
        if ($searchQuery) {
            $query = $postRepository->adminSearch($searchQuery);
        } elseif ($filter === 'bookmarks' && $user) {
            $query = $user->getBookmarkedPosts(); 
        } elseif ($filter === 'history') {
            $historyIds = $request->getSession()->get('post_history', []);
            if (empty($historyIds)) {
                $qb = $entityManager->getRepository(Post::class)->createQueryBuilder('p')->where('p.id = -1');
            } else {
                $qb = $entityManager->getRepository(Post::class)->createQueryBuilder('p')
                    ->where('p.id IN (:ids)')
                    ->setParameter('ids', $historyIds);
            }
        } elseif ($spaceId) {
            $qb = $entityManager->getRepository(Post::class)->createQueryBuilder('p')
                ->where('p.space = :spaceId')
                ->setParameter('spaceId', $spaceId);
        } elseif (in_array($filter, ['popular', 'unanswered'])) {
            // Keep your existing custom filters logic
            $query = $postRepository->findByFilter($filter);
        } else {
            // MAIN FEED: Apply QueryBuilder so sorting buttons work!
            $qb = $entityManager->getRepository(Post::class)->createQueryBuilder('p');
        }

        // Apply Sorting (Only if $qb is set, meaning we are on a feed that supports sorting)
        if (isset($qb)) {
            if ($sortBy === 'new') {
                $qb->orderBy('p.createdAt', 'DESC');
            } elseif ($sortBy === 'top') {
                $qb->orderBy('p.upvotes', 'DESC');
            } else {
                // 'hot': Prioritize score, use date as tie-breaker to keep feed fresh
                $qb->orderBy('p.upvotes', 'DESC')->addOrderBy('p.createdAt', 'DESC');
            }
            $query = $qb->getQuery();
        }

        $posts = $paginator->paginate($query, $request->query->getInt('page', 1), 5);
        $spaces = $entityManager->getRepository(\App\Entity\Forum\Space::class)->findAll();

        return $this->render('front/forum/index.html.twig', [
            'form' => $form->createView(),
            'posts' => $posts,
            'spaces' => $spaces,
            'searchQuery' => $searchQuery,
            'currentFilter' => $filter,
            'currentSpace' => $spaceId,
            'currentSort' => $sortBy,
        ]);
    }

    #[Route('/forum/guidelines', name: 'app_forum_guidelines')]
    public function guidelines(EntityManagerInterface $entityManager): Response
    {
        $spaces = $entityManager->getRepository(\App\Entity\Forum\Space::class)->findAll();

        return $this->render('front/forum/guidelines.html.twig', [
            'spaces' => $spaces,
            'currentFilter' => 'guidelines',
            'currentSpace' => null,
            'searchQuery' => null
        ]);
    }

    #[Route('/forum/about', name: 'app_forum_about')]
    public function about(EntityManagerInterface $entityManager): Response
    {
        $spaces = $entityManager->getRepository(\App\Entity\Forum\Space::class)->findAll();

        return $this->render('front/forum/about.html.twig', [
            'spaces' => $spaces,
            'currentFilter' => 'about',
            'currentSpace' => null,
            'searchQuery' => null
        ]);
    }

    #[Route('/forum/{id}', name: 'app_forum_show', requirements: ['id' => '\d+'])]
    public function show(
        ?Post $post,
        Request $request, 
        EntityManagerInterface $entityManager,
        CensorshipService $censorship
    ): Response
    {
        if (!$post) {
            return $this->render('front/forum/deleted.html.twig');
        }

        $session = $request->getSession();
        $history = $session->get('post_history', []);
        array_unshift($history, $post->getId()); 
        $history = array_unique($history); 
        $history = array_slice($history, 0, 30);
        $session->set('post_history', $history);

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

        $spaces = $entityManager->getRepository(\App\Entity\Forum\Space::class)->findAll();

        return $this->render('front/forum/show.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'spaces' => $spaces,
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

    #[Route('/forum/post/{id}/vote/{type}', name: 'app_forum_post_vote', methods: ['POST'])]
    public function votePost(Post $post, string $type, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'You must be logged in to vote.'], 403);
        }

        if ($type === 'up') {
            if ($post->isUpvotedBy($user)) {
                $post->removeUpvoter($user); 
            } else {
                $post->addUpvoter($user);    
                $post->removeDownvoter($user); 
            }
        } elseif ($type === 'down') {
            if ($post->isDownvotedBy($user)) {
                $post->removeDownvoter($user); 
            } else {
                $post->addDownvoter($user);    
                $post->removeUpvoter($user);   
            }
        }

        $entityManager->flush();

        return $this->json([
            'score' => $post->getUpvotes(),
            'upvoted' => $post->isUpvotedBy($user),
            'downvoted' => $post->isDownvotedBy($user)
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
        
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to report a post.');
            return $this->redirectToRoute('app_login');
        }

        if ($user === $post->getAuthor()) {
            $this->addFlash('warning', 'You cannot report your own post.');
            return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()]);
        }

        $reason = $request->request->get('reason', 'Inappropriate content');
        
        $report = new \App\Entity\Forum\Report();
        $report->setPost($post);
        $report->setReporter($user); 
        $report->setReason($reason);
        $report->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($report);
        $entityManager->flush();

        $this->addFlash('success', 'Thank you. The post has been reported to moderators.');
        return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()]);
    }

    #[Route('/forum/post/{id}/summary', name: 'app_forum_summary', methods: ['POST'])]
    public function summarize(Post $post, AiSummaryService $aiSummaryService): JsonResponse
    {
        $summary = $aiSummaryService->generateSummary($post);

        return $this->json([
            'summary' => $summary
        ]);
    }

    #[Route('/forum/post/{id}/bookmark', name: 'app_forum_post_bookmark', methods: ['POST'])]
    public function toggleBookmark(Post $post, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var \App\Entity\users\User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'You must be logged in to bookmark posts.'], 403);
        }

        if ($user->hasBookmarkedPost($post)) {
            $user->removeBookmarkedPost($post);
            $isBookmarked = false;
        } else {
            $user->addBookmarkedPost($post);
            $isBookmarked = true;
        }

        $entityManager->flush();

        return $this->json([
            'isBookmarked' => $isBookmarked,
            'message' => $isBookmarked ? 'Post saved to your reading list!' : 'Post removed from your reading list.'
        ]);
    }

    #[Route('/forum/space/{id}', name: 'app_forum_space')]
    public function space(
        \App\Entity\Forum\Space $space, 
        Request $request, 
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator,
        CensorshipService $censorship,
        OpenGraphFetcher $ogFetcher 
    ): Response
    {
        $post = new Post();
        $post->setSpace($space); 
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user) {
                $this->addFlash('error', 'You must be logged in to create a post.');
                return $this->redirectToRoute('app_login');
            }

            $post->setTitle($censorship->purify($post->getTitle()));
            $post->setContent($censorship->purify($post->getContent()));
            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setUpvotes(0);
            $post->setAuthor($user);
            $post->setIsLocked(false);

            if ($post->getLink()) {
                $ogData = $ogFetcher->fetch($post->getLink());
                if ($ogData) {
                    $post->setLinkTitle($ogData['title']);
                    $post->setLinkDescription($ogData['description']);
                    $post->setLinkImage($ogData['image']);
                }
            }

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Post created in ' . $space->getName() . '!');
            return $this->redirectToRoute('app_forum_space', ['id' => $space->getId()]);
        }

        $sortBy = $request->query->get('sortBy', 'hot');

        $qb = $entityManager->getRepository(Post::class)->createQueryBuilder('p')
            ->where('p.space = :space')
            ->setParameter('space', $space);

        if ($sortBy === 'new') {
            $qb->orderBy('p.createdAt', 'DESC');
        } elseif ($sortBy === 'top') {
            $qb->orderBy('p.upvotes', 'DESC');
        } else {
            $qb->orderBy('p.upvotes', 'DESC')
               ->addOrderBy('p.createdAt', 'DESC');
        }

        $posts = $paginator->paginate($qb->getQuery(), $request->query->getInt('page', 1), 5);

        $allSpaces = $entityManager->getRepository(\App\Entity\Forum\Space::class)->findAll();

        return $this->render('front/forum/space.html.twig', [
            'space' => $space,       
            'posts' => $posts,       
            'spaces' => $allSpaces,  
            'form' => $form->createView(),
            'currentSort' => $sortBy, 
        ]);
    }

    #[Route('/forum/ai/enhance', name: 'app_forum_ai_enhance', methods: ['POST'])]
    public function enhancePost(Request $request, AiSummaryService $aiService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $text = $data['text'] ?? '';

        if (empty(trim($text))) {
            return $this->json(['error' => 'No text provided.'], 400);
        }

        try {
            $enhancedText = $aiService->enhanceText($text);
            return $this->json(['enhancedText' => $enhancedText]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'AI is currently resting. Please try again.'], 500);
        }
    }
}