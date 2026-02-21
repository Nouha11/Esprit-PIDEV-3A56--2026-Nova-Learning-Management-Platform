<?php

namespace App\Repository;

use App\Entity\Quiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quiz>
 */
class QuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz::class);
    }

    /**
     * Find quizzes with filtering and sorting
     * 
     * @param array $filters
     * @param string $sortBy
     * @param string $sortOrder
     * @return Quiz[]
     */
    public function findWithFiltersAndSort(array $filters = [], string $sortBy = 'title', string $sortOrder = 'ASC'): array
    {
        $qb = $this->createQueryBuilder('q')
            ->leftJoin('q.questions', 'questions')
            ->addSelect('questions');

        // Apply text search filter
        if (!empty($filters['search'])) {
            $qb->andWhere('q.title LIKE :search OR q.description LIKE :search')
               ->setParameter('search', '%' . $filters['search'] . '%');
        }

        // Group by is needed for question count filters and sorting
        $needsGroupBy = !empty($filters['minQuestions']) || !empty($filters['maxQuestions']) || $sortBy === 'questionCount';
        
        if ($needsGroupBy) {
            $qb->groupBy('q.id');
        }

        // Apply question count filters using HAVING
        if (!empty($filters['minQuestions']) && !empty($filters['maxQuestions'])) {
            $qb->having('COUNT(questions.id) >= :minQuestions AND COUNT(questions.id) <= :maxQuestions')
               ->setParameter('minQuestions', (int)$filters['minQuestions'])
               ->setParameter('maxQuestions', (int)$filters['maxQuestions']);
        } elseif (!empty($filters['minQuestions'])) {
            $qb->having('COUNT(questions.id) >= :minQuestions')
               ->setParameter('minQuestions', (int)$filters['minQuestions']);
        } elseif (!empty($filters['maxQuestions'])) {
            $qb->having('COUNT(questions.id) <= :maxQuestions')
               ->setParameter('maxQuestions', (int)$filters['maxQuestions']);
        }

        // Apply sorting
        $validSortFields = ['title', 'id', 'questionCount'];
        $validSortOrders = ['ASC', 'DESC'];
        
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'title';
        }
        
        $sortOrder = strtoupper($sortOrder);
        if (!in_array($sortOrder, $validSortOrders)) {
            $sortOrder = 'ASC';
        }

        if ($sortBy === 'questionCount') {
            $qb->addSelect('COUNT(questions.id) as HIDDEN questionCount')
               ->orderBy('questionCount', $sortOrder);
        } else {
            $qb->orderBy('q.' . $sortBy, $sortOrder);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get quiz statistics for filtering UI
     */
    public function getQuizStatistics(): array
    {
        // Get total quizzes
        $totalQuizzes = $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($totalQuizzes == 0) {
            return [
                'totalQuizzes' => 0,
                'minQuestions' => 0,
                'maxQuestions' => 0,
                'avgQuestions' => 0
            ];
        }

        // Get question count statistics per quiz
        $questionCounts = $this->createQueryBuilder('q')
            ->leftJoin('q.questions', 'questions')
            ->select('q.id')
            ->addSelect('COUNT(questions.id) as questionCount')
            ->groupBy('q.id')
            ->getQuery()
            ->getResult();

        $counts = array_column($questionCounts, 'questionCount');
        
        return [
            'totalQuizzes' => (int)$totalQuizzes,
            'minQuestions' => !empty($counts) ? (int)min($counts) : 0,
            'maxQuestions' => !empty($counts) ? (int)max($counts) : 0,
            'avgQuestions' => !empty($counts) ? round(array_sum($counts) / count($counts), 1) : 0
        ];
    }

//    /**
//     * @return Quiz[] Returns an array of Quiz objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('q.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Quiz
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
