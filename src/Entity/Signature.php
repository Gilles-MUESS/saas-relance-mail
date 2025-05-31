<?php

namespace App\Entity;

use App\Repository\SignatureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SignatureRepository::class) ]
class Signature {
	#[ORM\Id ]
	#[ORM\GeneratedValue ]
	#[ORM\Column ]
	private ?int $id = null;

	#[ORM\Column(length: 255) ]
	private ?string $title = null;

	#[ORM\Column(type: Types::TEXT) ]
	private ?string $content = null;

	#[ORM\ManyToOne(inversedBy: 'signatures') ]
	#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE') ]
	private ?User $user = null;

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

	public function getContent(): ?string {
		return $this->content;
	}

	public function setContent( string $content ): static {
		$this->content = $content;

		return $this;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser( ?User $user ): static {
		$this->user = $user;

		return $this;
	}
}
