<?php

namespace App\Service\Email;

use App\Entity\Message;
use App\Entity\QueuedEmail;
use Psr\Log\LoggerInterface;
use App\Message\ProcessMessage;
use App\Entity\UserEmailAccount;
use App\Mailer\Provider\ProviderManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\QueuedEmailRepository;
use Symfony\Component\Messenger\MessageBusInterface;

class EmailQueueManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
        private QueuedEmailRepository $queuedEmailRepository,
        private ProviderManager $providerManager
    ) {}

    /**
     * Ajoute un email à la file d'attente pour un compte spécifique
     *
     * @param Message $message Le message à envoyer
     * @param UserEmailAccount $account Le compte email à utiliser pour l'envoi
     * @param \DateTimeInterface|null $scheduledAt Date d'envoi programmée (par défaut: maintenant)
     * @return bool True si l'ajout a réussi, false sinon
     * @throws \InvalidArgumentException Si le compte email n'est pas valide ou désactivé
     */
    public function queueEmail(
        Message $message,
        UserEmailAccount $account,
        ?\DateTimeInterface $scheduledAt = null
    ): bool {
        try {
            // Vérifier que le compte est valide et activé
            if (empty($account->getAccessToken()) && empty($account->getRefreshToken())) {
                throw new \InvalidArgumentException('Le compte email n\'est pas activé');
            }

            if (null === $scheduledAt) {
                $scheduledAt = new \DateTimeImmutable();
            }

            $queuedEmail = new QueuedEmail();
            $queuedEmail->setMessage($message);
            $queuedEmail->setAccount($account);
            $queuedEmail->setScheduledAt($scheduledAt);
            $queuedEmail->setStatus(QueuedEmail::STATUS_PENDING);
            $queuedEmail->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($queuedEmail);
            $this->entityManager->flush();

            // Si la date d'envoi est maintenant ou dans le passé, on traite immédiatement
            if ($scheduledAt <= new \DateTimeImmutable()) {
                $this->dispatchMessage(new ProcessMessage($queuedEmail->getId()));
            }

            $this->logger->info('Email ajouté à la file d\'attente', [
                'queued_email_id' => $queuedEmail->getId(),
                'message_id' => $message->getId(),
                'account_id' => $account->getId(),
                'scheduled_at' => $scheduledAt->format(\DateTimeInterface::ATOM)
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'ajout d\'un email à la file d\'attente', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'message_id' => $message->getId(),
                'account_id' => $account->getId()
            ]);

            if ($e instanceof \InvalidArgumentException) {
                throw $e; // On propage les erreurs de validation
            }

            return false;
        }
    }

    /**
     * Traite les emails en attente
     *
     * @param int $limit Nombre maximum d'emails à traiter
     * @return int Nombre d'emails traités
     */
    public function processQueue(int $limit = 100): int
    {
        $processed = 0;
        $now = new \DateTimeImmutable();

        try {
            // Trouver les emails en attente
            $queuedEmails = $this->queuedEmailRepository->findPendingEmails($limit);

            foreach ($queuedEmails as $queuedEmail) {
                try {
                    // Vérifier que le compte email est toujours valide
                    $account = $queuedEmail->getAccount();
                    if (empty($account->getAccessToken())) {
                        // Pas de token, on ne peut rien faire
                        $this->handleInvalidAccount($queuedEmail, $account, 'Aucun token d\'accès trouvé');
                        $processed++;
                        continue;
                    }

                    // Si le token est expiré mais qu'on a un refresh token, on tente de le rafraîchir
                    if (
                        $account->getAccessTokenExpiry() !== null &&
                        $account->getAccessTokenExpiry() < new \DateTimeImmutable()
                    ) {

                        if ($this->providerManager->refreshTokenIfNeeded($account)) {
                            $this->entityManager->flush();
                            $this->logger->info('Token rafraîchi avec succès', ['account_id' => $account->getId()]);
                        } else {
                            $this->handleInvalidAccount($queuedEmail, $account, 'Échec du rafraîchissement du token');
                            $processed++;
                            continue;
                        }
                    }
                    // Si le token est expiré et qu'on ne peut pas le rafraîchir
                    else if (
                        $account->getAccessTokenExpiry() !== null &&
                        $account->getAccessTokenExpiry() < new \DateTimeImmutable()
                    ) {
                        $this->handleInvalidAccount($queuedEmail, $account, 'Token expiré et aucun refresh token disponible');
                        $processed++;
                        continue;
                    }

                    // Marquer comme en cours de traitement
                    $queuedEmail->setStatus(QueuedEmail::STATUS_PROCESSING);
                    $queuedEmail->setProcessingStartedAt(new \DateTimeImmutable());
                    $this->entityManager->flush();

                    // Dispatcher le message pour le traitement asynchrone
                    $this->dispatchMessage(new ProcessMessage($queuedEmail->getId()));
                    $processed++;
                } catch (\Exception $e) {
                    $this->log('error', 'Error processing queued email: ' . $e->getMessage(), [
                        'queued_email_id' => $queuedEmail->getId(),
                        'exception' => $e
                    ]);

                    // Marquer comme erreur après plusieurs tentatives échouées
                    if ($queuedEmail->getRetryCount() >= 3) {
                        $queuedEmail->setStatus(QueuedEmail::STATUS_FAILED);
                        $queuedEmail->setError($e->getMessage());
                        $this->entityManager->flush();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->log('error', 'Error in queue processing: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }

        return $processed;
    }

    /**
     * Traite un email spécifique de la file d'attente
     */
    public function processQueuedEmail(QueuedEmail $queuedEmail, EmailManager $emailManager): bool
    {
        try {
            $message = $queuedEmail->getMessage();
            $account = $queuedEmail->getAccount();

            // Vérifier si le compte est toujours actif
            // if (!$account->isActive()) {
            //     throw new \RuntimeException('Account is not active');
            // }

            // Envoyer l'email
            $result = $emailManager->sendEmail($message, $account);

            // Mettre à jour le statut
            if ($result) {
                $queuedEmail->setStatus(QueuedEmail::STATUS_SENT);
                $queuedEmail->setSentAt(new \DateTimeImmutable());
            } else {
                throw new \RuntimeException('Failed to send email');
            }

            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            $queuedEmail->incrementRetryCount();
            $queuedEmail->setError($e->getMessage());

            // Après plusieurs tentatives, marquer comme échoué
            if ($queuedEmail->getRetryCount() >= 3) {
                $queuedEmail->setStatus(QueuedEmail::STATUS_FAILED);
            }

            $this->entityManager->flush();

            $this->log('error', 'Failed to process queued email: ' . $e->getMessage(), [
                'queued_email_id' => $queuedEmail->getId(),
                'exception' => $e
            ]);

            return false;
        }
    }

    /**
     * Envoie un message au bus de messages
     */
    private function dispatchMessage(object $message): void
    {
        try {
            $this->messageBus->dispatch($message);
        } catch (\Exception $e) {
            $this->log('error', 'Failed to dispatch message: ' . $e->getMessage(), [
                'exception' => $e,
                'message_class' => get_class($message)
            ]);
            throw $e;
        }
    }

    // Méthode pour gérer un compte invalide
    private function handleInvalidAccount(QueuedEmail $queuedEmail, ?UserEmailAccount $account, string $reason): void
    {
        $accountId = $account ? $account->getId() : null;
        $this->logger->warning('Compte email invalide: ' . $reason, [
            'queued_email_id' => $queuedEmail->getId(),
            'account_id' => $accountId
        ]);

        $this->entityManager->remove($queuedEmail);
        $this->entityManager->flush();
    }



    /**
     * Journalisation des événements
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->$level($message, $context);
        }
    }
}
