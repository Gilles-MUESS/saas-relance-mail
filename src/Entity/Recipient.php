<?php

namespace App\Entity;

use App\Repository\RecipientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipientRepository::class) ]
class Recipient {
	#[ORM\Id ]
	#[ORM\GeneratedValue ]
	#[ORM\Column ]
	private ?int $id = null;

	#[ORM\Column(length: 255) ]
	private ?string $firstName = null;

	#[ORM\Column(length: 255) ]
	private ?string $lastName = null;

	#[ORM\Column(length: 255) ]
	private ?string $email = null;

	#[ORM\ManyToOne(inversedBy: 'recipients') ]
	#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE') ]
	private ?User $user = null;

	/**
	 * @var Collection<int, Sequence>
	 */
	#[ORM\ManyToMany(targetEntity: Sequence::class, mappedBy: 'recipient') ]
	private Collection $sequences;

	public function __construct() {
		$this->sequences = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getFirstName(): ?string {
		return $this->firstName;
	}

	public function setFirstName( string $firstName ): static {
		$this->firstName = $firstName;

		return $this;
	}

	public function getLastName(): ?string {
		return $this->lastName;
	}

	public function setLastName( string $lastName ): static {
		$this->lastName = $lastName;

		return $this;
	}

	public function getEmail(): ?string {
		return $this->email;
	}

	public function setEmail( string $email ): static {
		$this->email = $email;

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
	 * @return Collection<int, Sequence>
	 */
	public function getSequences(): Collection {
		return $this->sequences;
	}

	public function addSequence( Sequence $sequence ): static {
		if ( ! $this->sequences->contains( $sequence ) ) {
			$this->sequences->add( $sequence );
			$sequence->addRecipient( $this );
		}

		return $this;
	}

	public function removeSequence( Sequence $sequence ): static {
		if ( $this->sequences->removeElement( $sequence ) ) {
			$sequence->removeRecipient( $this );
		}

		return $this;
	}
}
