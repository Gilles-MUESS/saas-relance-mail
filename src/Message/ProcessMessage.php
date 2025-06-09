<?php

namespace App\Message;

class ProcessMessage
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
