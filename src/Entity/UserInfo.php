<?php

namespace App\Entity;

use App\Repository\UserInfoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserInfoRepository::class) ]
class UserInfo {
	#[ORM\Id ]
	#[ORM\GeneratedValue ]
	#[ORM\Column ]
	private ?int $id = null;

	#[ORM\Column(length: 255) ]
	private ?string $firstName = null;

	#[ORM\Column(length: 255) ]
	private ?string $lastName = null;

	#[ORM\Column(length: 255, nullable: true) ]
	private ?string $company = null;

	#[ORM\Column(length: 255, nullable: true) ]
	private ?string $address = null;

	#[ORM\Column(length: 255, nullable: true) ]
	private ?string $cp = null;

	#[ORM\Column(length: 255, nullable: true) ]
	private ?string $city = null;

	#[ORM\OneToOne(inversedBy: 'userInfo', cascade: [ 'persist', 'remove' ]) ]
	#[ORM\JoinColumn(nullable: false) ]
	private ?User $user = null;

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

	public function getCompany(): ?string {
		return $this->company;
	}

	public function setCompany( ?string $company ): static {
		$this->company = $company;

		return $this;
	}

	public function getAddress(): ?string {
		return $this->address;
	}

	public function setAddress( ?string $address ): static {
		$this->address = $address;

		return $this;
	}

	public function getCp(): ?string {
		return $this->cp;
	}

	public function setCp( ?string $cp ): static {
		$this->cp = $cp;

		return $this;
	}

	public function getCity(): ?string {
		return $this->city;
	}

	public function setCity( ?string $city ): static {
		$this->city = $city;

		return $this;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser( User $user ): static {
		$this->user = $user;

		return $this;
	}
}
