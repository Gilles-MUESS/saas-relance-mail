<?php

namespace App\EventSubscriber;

use App\Entity\Message;
use App\Service\Email\EmailQueueManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsDoctrineListener(Events::postPersist)]
#[AsDoctrineListener(Events::postUpdate)]
class MessageSubscriber
{
    public function __construct(
        private EmailQueueManager $emailQueueManager
    ) {}

    public function __invoke(LifecycleEventArgs $args): void
    {
        $this->handleMessage($args);
    }

    private function handleMessage(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // Ne traiter que les entités Message
        if (!$entity instanceof Message) {
            return;
        }

        $sequence = $entity->getSequence();

        // Vérifier si le message appartient à une séquence active
        if ($sequence && $sequence->getStatus() === \App\Entity\Sequence::STATUS_ACTIVE) {
            $account = $sequence->getUserEmailAccount();

            // Vérifier que le compte est valide
            if ($account && $account->getAccessToken()) {
                try {
                    $this->emailQueueManager->queueEmail(
                        $entity,
                        $account,
                        $entity->getSendAt()
                    );
                } catch (\Exception $e) {
                    // Loguer l'erreur mais ne pas interrompre le flux
                    error_log(sprintf(
                        'Erreur lors de l\'ajout automatique du message à la file d\'attente (message ID: %d): %s',
                        $entity->getId(),
                        $e->getMessage()
                    ));
                }
            }
        }
    }
}
