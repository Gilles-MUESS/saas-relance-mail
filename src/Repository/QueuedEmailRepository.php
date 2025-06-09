<?php

namespace App\Repository;

use App\Entity\QueuedEmail;
use App\Entity\UserEmailAccount;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<QueuedEmail>
 *
 * @method QueuedEmail|null find($id, $lockMode = null, $lockVersion = null)
 * @method QueuedEmail|null findOneBy(array $criteria, array $orderBy = null)
 * @method QueuedEmail[]    findAll()
 * @method QueuedEmail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QueuedEmailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QueuedEmail::class);
    }

    /**
     * Trouve les emails en attente qui peuvent être traités
     *
     * @param int $limit Nombre maximum d'emails à retourner
     * @return QueuedEmail[]
     */
    public function findPendingEmails(int $limit = 100): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.status = :status')
            ->andWhere('q.scheduledAt <= :now')
            ->setParameter('status', QueuedEmail::STATUS_PENDING)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('q.scheduledAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les emails en cours de traitement depuis trop longtemps
     *
     * @param int $timeoutMinutes Temps en minutes après lequel un email est considéré comme bloqué
     * @param int $limit Nombre maximum d'emails à retourner
     * @return QueuedEmail[]
     */
    public function findStalledEmails(int $timeoutMinutes = 30, int $limit = 100): array
    {
        $timeout = new \DateTimeImmutable("-$timeoutMinutes minutes");

        return $this->createQueryBuilder('q')
            ->where('q.status = :status')
            ->andWhere('q.processingStartedAt < :timeout')
            ->setParameter('status', QueuedEmail::STATUS_PROCESSING)
            ->setParameter('timeout', $timeout)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre d'emails en attente par compte
     *
     * @param UserEmailAccount $account
     * @return int
     */
    public function countPendingByAccount(UserEmailAccount $account): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->where('q.account = :account')
            ->andWhere('q.status = :status')
            ->setParameter('account', $account)
            ->setParameter('status', QueuedEmail::STATUS_PENDING)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les emails échoués pouvant être réessayés
     *
     * @param int $maxRetries Nombre maximum de tentatives
     * @param int $retryDelayMinutes Délai minimum entre les tentatives en minutes
     * @param int $limit Nombre maximum d'emails à retourner
     * @return QueuedEmail[]
     */
    public function findRetryableFailedEmails(
        int $maxRetries = 3,
        int $retryDelayMinutes = 60,
        int $limit = 100
    ): array {
        $retryAfter = new \DateTimeImmutable("-$retryDelayMinutes minutes");

        return $this->createQueryBuilder('q')
            ->where('q.status = :status')
            ->andWhere('q.retryCount < :maxRetries')
            ->andWhere('q.processingStartedAt < :retryAfter')
            ->setParameter('status', QueuedEmail::STATUS_FAILED)
            ->setParameter('maxRetries', $maxRetries)
            ->setParameter('retryAfter', $retryAfter)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
