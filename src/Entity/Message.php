<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MessageRepository;

#[ORM\Entity(repositoryClass: MessageRepository::class) ]
class Message {
	#[ORM\Id ]
	#[ORM\GeneratedValue ]
	#[ORM\Column ]
	private ?int $id = null;

	#[Assert\Range(
		min: 'now',
		notInRangeMessage: 'La date ne peut pas être antérieure à aujourd\'hui.'
	) ]
	#[ORM\Column(type: Types::DATE_IMMUTABLE) ]
	private ?\DateTimeImmutable $sendAt = null;

	#[ORM\Column(type: Types::TIME_IMMUTABLE) ]
	private ?\DateTimeImmutable $sendAtTime = null;

	#[ORM\Column(length: 255) ]
	private ?string $subject = null;

	#[ORM\Column(type: Types::TEXT) ]
	private ?string $message = null;

	#[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true) ]
	private ?array $attachment = null;

	#[ORM\ManyToOne(inversedBy: 'messages') ]
	#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE') ]
	private ?Sequence $sequence = null;

	#[ORM\Column ]
	private ?bool $isSent = null;

	public function getId(): ?int {
		return $this->id;
	}

	public function getSendAt(): ?\DateTimeImmutable {
		return $this->sendAt;
	}

	public function setSendAt( \DateTimeImmutable $sendAt ): static {
		$this->sendAt = $sendAt;

		return $this;
	}

	public function getSendAtTime(): ?\DateTimeImmutable {
		return $this->sendAtTime;
	}

	public function setSendAtTime( \DateTimeImmutable $sendAtTime ): static {
		$this->sendAtTime = $sendAtTime;

		return $this;
	}

	public function getSubject(): ?string {
		return $this->subject;
	}

	public function setSubject( string $subject ): static {
		$this->subject = $subject;

		return $this;
	}

	public function getMessage(): ?string {
		return $this->message;
	}

	public function setMessage( string $message ): static {
		$this->message = $message;

		return $this;
	}

	public function getAttachment(): ?array {
		return $this->attachment;
	}

	public function setAttachment( ?array $attachment ): static {
		$this->attachment = $attachment;

		return $this;
	}

	public function getSequence(): ?Sequence {
		return $this->sequence;
	}

	public function setSequence( ?Sequence $sequence ): static {
		$this->sequence = $sequence;

		return $this;
	}

	public function getIsSent(): ?bool {
		return $this->isSent;
	}

	public function setIsSent( bool $isSent = false ): static {
		$this->isSent = $isSent;

		return $this;
	}
}
