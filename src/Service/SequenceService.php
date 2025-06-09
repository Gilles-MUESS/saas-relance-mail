<?php

namespace App\Service;

use App\Entity\Sequence;

use App\Service\Email\EmailQueueManager;

class SequenceService {
    public function __construct(
        private EmailQueueManager $emailQueueManager
    ) {
    }
	public function getSequencesByStatus( array $sequences, string $status ) {
		$filteredSequences = [];
		foreach ( $sequences as $sequence ) {
			if ( $sequence->getStatus() === $status ) {
				$filteredSequences[] = $sequence;
			}
		}
		return $filteredSequences;
	}

	public function getNextMessageDate( Sequence $sequence ): ?\DateTimeInterface {
		$now = new \DateTime();
		$nextDate = null;

		foreach ( $sequence->getMessages() as $message ) {
			$sendAt = $message->getSendAt();
			if ( $sendAt > $now && ( $nextDate === null || $sendAt < $nextDate ) ) {
				$nextDate = $sendAt;
			}
		}

		return $nextDate;
	}

    /**
     * Active une séquence et ajoute tous ses messages à la file d'attente
     *
     * @param Sequence $sequence La séquence à activer
     * @return bool True si l'activation a réussi, false sinon
     */
    public function activateSequence(Sequence $sequence): bool
    {
        $account = $sequence->getUserEmailAccount();

        // Vérifier que la séquence a un compte email valide
        if (!$account || !$account->getAccessToken()) {
            throw new \RuntimeException('Impossible d\'activer la séquence : aucun compte email valide associé');
        }

        // Vérifier que la séquence a des messages
        if ($sequence->getMessages()->isEmpty()) {
            throw new \RuntimeException('Impossible d\'activer la séquence : aucun message trouvé');
        }

        $success = true;

        // Ajouter chaque message à la file d'attente
        foreach ($sequence->getMessages() as $message) {
            try {
                $sendAt = $message->getSendAt();
                $this->emailQueueManager->queueEmail($message, $account, $sendAt);
            } catch (\Exception $e) {
                // Loguer l'erreur mais continuer avec les autres messages
                error_log(sprintf(
                    'Erreur lors de l\'ajout du message à la file d\'attente (message ID: %d): %s',
                    $message->getId(),
                    $e->getMessage()
                ));
                $success = false;
            }
        }

        return $success;
    }
}
