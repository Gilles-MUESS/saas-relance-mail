<?php

namespace App\Message;

/**
 * Message envoyé dans le bus de messages pour traiter un email en file d'attente
 */
class ProcessEmailMessage
{
    private int $queuedEmailId;

    public function __construct(int $queuedEmailId)
    {
        $this->queuedEmailId = $queuedEmailId;
    }

    public function getQueuedEmailId(): int
    {
        return $this->queuedEmailId;
    }
}
