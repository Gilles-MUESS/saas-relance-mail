<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository {
	public function __construct( ManagerRegistry $registry ) {
		parent::__construct( $registry, Message::class);
	}

	/**
	 * Find messages by date when it's inferior or equal to now. 
	 *
	 * @param  \DateTimeInterface $now
	 * @return ?Message[] Returns an array of Message objects
	 */
	public function findToSend( \DateTimeInterface $now ): ?array {
		return $this->createQueryBuilder( 'm' )
			->where( 'm.sendAt <= :now' )
			->andWhere( 'm.isSent = false' ) // si tu as un champ pour éviter les doublons d'envoi
			->setParameter( 'now', $now )
			->getQuery()
			->getResult();
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
