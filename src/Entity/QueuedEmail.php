<?php

namespace App\Entity;

use App\Repository\QueuedEmailRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Représente un email en file d'attente pour envoi différé
 */
#[ORM\Entity(repositoryClass: QueuedEmailRepository::class)]
#[ORM\Table(name: 'queued_email')]
#[ORM\Index(columns: ['status', 'scheduled_at'], name: 'idx_queued_email_status_scheduled')]
#[ORM\Index(columns: ['account_id'], name: 'idx_queued_email_account')]
class QueuedEmail
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Message::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Message $message;

    #[ORM\ManyToOne(targetEntity: UserEmailAccount::class, inversedBy: 'queuedEmails')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private UserEmailAccount $account;

    /**
     * Date à laquelle l'email doit être envoyé
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeInterface $scheduledAt;

    /**
     * Date de création de l'entrée
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeInterface $createdAt;

    /**
     * Date de début de traitement
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $processingStartedAt = null;

    /**
     * Date d'envoi effectif
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $sentAt = null;

    /**
     * Statut actuel
     */
    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $status = self::STATUS_PENDING;

    /**
     * Nombre de tentatives d'envoi
     */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $retryCount = 0;

    /**
     * Dernière erreur survenue
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $error = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function setMessage(Message $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getAccount(): UserEmailAccount
    {
        return $this->account;
    }

    public function setAccount(UserEmailAccount $account): self
    {
        $this->account = $account;
        return $this;
    }

    public function getScheduledAt(): \DateTimeInterface
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(\DateTimeInterface $scheduledAt): self
    {
        $this->scheduledAt = $scheduledAt;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getProcessingStartedAt(): ?\DateTimeInterface
    {
        return $this->processingStartedAt;
    }

    public function setProcessingStartedAt(\DateTimeInterface $processingStartedAt): self
    {
        $this->processingStartedAt = $processingStartedAt;
        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeInterface $sentAt): self
    {
        $this->sentAt = $sentAt;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_SENT,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED
        ])) {
            throw new \InvalidArgumentException("Invalid status: $status");
        }

        $this->status = $status;
        return $this;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    public function incrementRetryCount(): self
    {
        $this->retryCount++;
        return $this;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): self
    {
        $this->error = $error;
        return $this;
    }

    /**
     * Vérifie si l'email peut être traité
     */
    public function canBeProcessed(): bool
    {
        return $this->status === self::STATUS_PENDING &&
               $this->scheduledAt <= new \DateTimeImmutable();
    }
}
