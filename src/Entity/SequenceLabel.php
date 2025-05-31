<?php

namespace App\Entity;

use App\Repository\SequenceLabelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SequenceLabelRepository::class) ]
class SequenceLabel {
	#[ORM\Id ]
	#[ORM\GeneratedValue ]
	#[ORM\Column ]
	private ?int $id = null;

	#[ORM\Column(length: 255) ]
	private ?string $title = null;

	#[ORM\ManyToOne ]
	private ?Color $color = null;

	/**
	 * @var Collection<int, User>
	 */
	#[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'sequenceLabels') ]
	private Collection $user;

	/**
	 * @var Collection<int, Sequence>
	 */
	#[ORM\OneToMany(targetEntity: Sequence::class, mappedBy: 'label') ]
	private Collection $sequences;

	public function __construct() {
		$this->user = new ArrayCollection();
		$this->sequences = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getTitle(): ?string {
		return $this->title;
	}

	public function setTitle( string $title ): static {
		$this->title = $title;

		return $this;
	}

	public function getColor(): ?Color {
		return $this->color;
	}

	public function setColor( ?Color $color ): static {
		$this->color = $color;

		return $this;
	}

	/**
	 * @return Collection<int, User>
	 */
	public function getUser(): Collection {
		return $this->user;
	}

	public function addUser( User $user ): static {
		if ( ! $this->user->contains( $user ) ) {
			$this->user->add( $user );
		}

		return $this;
	}

	public function removeUser( User $user ): static {
		$this->user->removeElement( $user );

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
			$sequence->setLabel( $this );
		}

		return $this;
	}

	public function removeSequence( Sequence $sequence ): static {
		if ( $this->sequences->removeElement( $sequence ) ) {
			// set the owning side to null (unless already changed)
			if ( $sequence->getLabel() === $this ) {
				$sequence->setLabel( null );
			}
		}

		return $this;
	}
}
