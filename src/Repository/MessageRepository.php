<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Find messages by date when it's inferior or equal to now.
     *
     * @param  \DateTimeInterface $now
     * @return Message[] Returns an array of Message objects
     */
    public function findToSend(\DateTimeInterface $now): array
    {
        $date = $now->format('Y-m-d');
        $time = \DateTimeImmutable::createFromFormat('H:i:s', $now->format('H:i:s'));

        $qb = $this->createQueryBuilder('m');
        $qb
            ->where('m.isSent = false')
            ->andWhere(
                $qb->expr()->orX(
                    'm.sendAt < :date',
                    $qb->expr()->andX(
                        'm.sendAt = :date',
                        'm.sendAtTime <= :time'
                    )
                )
            )
            ->setParameter('date', $date)
            ->setParameter('time', $time, \Doctrine\DBAL\Types\Types::TIME_IMMUTABLE);

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Message[] Returns an array of Message objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Message
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
