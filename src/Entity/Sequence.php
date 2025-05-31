<?php

namespace App\Entity;

use App\Repository\SequenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SequenceRepository::class) ]
class Sequence {
	#[ORM\Id ]
	#[ORM\GeneratedValue ]
	#[ORM\Column ]
	private ?int $id = null;

	#[ORM\Column ]
	private ?\DateTimeImmutable $CreatedAt = null;

	public const STATUS_DRAFT = 'A venir';
	public const STATUS_ACTIVE = 'En cours';
	public const STATUS_ARCHIVED = 'Archivé';
	public const STATUS_FAIL = 'Echoué';
	public const STATUS_SUCCESS = 'Terminé';
	public const STATUSES = [ 
		self::STATUS_DRAFT,
		self::STATUS_ACTIVE,
		self::STATUS_ARCHIVED,
		self::STATUS_FAIL,
		self::STATUS_SUCCESS,
	];

	#[ORM\Column(length: 255) ]
	private ?string $status = null;

	#[ORM\ManyToOne(inversedBy: 'sequences') ]
	private ?SequenceLabel $label = null;

	#[ORM\ManyToOne(inversedBy: 'sequences') ]
	#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE') ]
	private ?User $user = null;

	/**
	 * @var Collection<int, Message>
	 */
	#[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'sequence', orphanRemoval: true, cascade: [ 'persist', 'remove' ]) ]
	#[ORM\JoinColumn(onDelete: 'CASCADE') ]
	private Collection $messages;

	/**
	 * @var Collection<int, Recipient>
	 */
	#[ORM\ManyToMany(targetEntity: Recipient::class, inversedBy: 'sequences') ]
	private Collection $recipient;

	public function __construct() {
		$this->messages = new ArrayCollection();
		$this->recipient = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getCreatedAt(): ?\DateTimeImmutable {
		return $this->CreatedAt;
	}

	public function setCreatedAt( \DateTimeImmutable $CreatedAt ): static {
		$this->CreatedAt = $CreatedAt;

		return $this;
	}

	public function getStatus(): ?string {
		return $this->status;
	}

	public function setStatus( ?string $status ): static {
		$this->status = $status;

		return $this;
	}

	public function getLabel(): ?SequenceLabel {
		return $this->label;
	}

	public function setLabel( ?SequenceLabel $label ): static {
		$this->label = $label;

		return $this;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser( ?User $user ): static {
		$this->user = $user;

		return $this;
	}

	/**
	 * @return Collection<int, Message>
	 */
	public function getMessages(): Collection {
		return $this->messages;
	}

	public function addMessage( Message $message ): static {
		if ( ! $this->messages->contains( $message ) ) {
			$this->messages->add( $message );
			$message->setSequence( $this );
		}

		return $this;
	}

	public function removeMessage( Message $message ): static {
		if ( $this->messages->removeElement( $message ) ) {
			// set the owning side to null (unless already changed)
			if ( $message->getSequence() === $this ) {
				$message->setSequence( null );
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int, Recipient>
	 */
	public function getRecipient(): Collection {
		return $this->recipient;
	}

	public function addRecipient( Recipient $recipient ): static {
		if ( ! $this->recipient->contains( $recipient ) ) {
			$this->recipient->add( $recipient );
		}

		return $this;
	}

	public function removeRecipient( Recipient $recipient ): static {
		$this->recipient->removeElement( $recipient );

		return $this;
	}
}
