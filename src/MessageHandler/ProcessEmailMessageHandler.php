<?php

namespace App\MessageHandler;

use App\Entity\QueuedEmail;
use Psr\Log\LoggerInterface;
use App\Service\Email\EmailManager;
use App\Message\ProcessEmailMessage;
use App\Service\Email\EmailQueueManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Gestionnaire pour traiter les messages en file d'attente
 */
#[AsMessageHandler]
class ProcessEmailMessageHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EmailQueueManager $queueManager,
        private EmailManager $emailManager,
        private ?LoggerInterface $logger = null
    ) {
    }

    public function __invoke(ProcessEmailMessage $message)
    {
        $queuedEmail = $this->entityManager->getRepository(QueuedEmail::class)
            ->find($message->getQueuedEmailId());

        if (!$queuedEmail) {
            $this->log('error', 'Queued email not found', [
                'id' => $message->getQueuedEmailId()
            ]);
            return;
        }

        // Vérifier si le message est déjà en cours de traitement
        if ($queuedEmail->getStatus() === QueuedEmail::STATUS_PROCESSING) {
            $this->log('debug', 'Email already being processed', [
                'id' => $queuedEmail->getId()
            ]);
            return;
        }

        try {
            $this->entityManager->beginTransaction();

            // Verrouiller la ligne pour éviter les traitements en double
            $this->entityManager->getConnection()->executeQuery(
                'SELECT 1 FROM queued_email WHERE id = :id FOR UPDATE',
                ['id' => $queuedEmail->getId()],
                ['id' => 'integer']
            );

            // Recharger l'entité pour s'assurer d'avoir les dernières données
            $this->entityManager->refresh($queuedEmail);

            // Vérifier à nouveau l'état après le verrouillage
            if ($queuedEmail->getStatus() !== QueuedEmail::STATUS_PENDING &&
                $queuedEmail->getStatus() !== QueuedEmail::STATUS_PROCESSING) {
                $this->log('debug', 'Email already processed', [
                    'id' => $queuedEmail->getId(),
                    'status' => $queuedEmail->getStatus()
                ]);
                $this->entityManager->rollback();
                return;
            }

            // Marquer comme en cours de traitement
            $queuedEmail->setStatus(QueuedEmail::STATUS_PROCESSING);
            $queuedEmail->setProcessingStartedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $this->log('error', 'Error starting email processing: ' . $e->getMessage(), [
                'id' => $queuedEmail->getId(),
                'exception' => $e
            ]);
            return;
        }

        // Traiter l'email
        try {
            $this->queueManager->processQueuedEmail($queuedEmail, $this->emailManager);
        } catch (\Exception $e) {
            $this->log('error', 'Error processing email: ' . $e->getMessage(), [
                'id' => $queuedEmail->getId(),
                'exception' => $e
            ]);
        }
    }

    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->$level($message, $context);
        }
    }
}
