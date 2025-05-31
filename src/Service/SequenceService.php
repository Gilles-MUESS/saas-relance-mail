<?php

namespace App\Service;

use App\Entity\Sequence;

class SequenceService {
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
}
