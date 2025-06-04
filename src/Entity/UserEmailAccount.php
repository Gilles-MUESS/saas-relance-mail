<?php

namespace App\Entity;

use App\Repository\UserEmailAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserEmailAccountRepository::class) ]
class UserEmailAccount {
	#[ORM\Id ]
	#[ORM\GeneratedValue ]
	#[ORM\Column ]
	private ?int $id = null;

	#[ORM\Column(length: 255, nullable: true) ]
	private ?string $provider = null;

	#[ORM\Column(length: 255, nullable: true) ]
	private ?string $email = null;

	#[ORM\Column(length: 255) ]
	private ?string $access_token = null;

	#[ORM\Column(length: 255, nullable: true) ]
	private ?string $refresh_token = null;

	#[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true) ]
	private ?\DateTimeImmutable $access_token_expiry = null;

	#[ORM\Column(length: 255, nullable: true) ]
	private ?string $provider_user_id = null;

	#[ORM\ManyToOne(inversedBy: 'userEmailAccounts') ]
	#[ORM\JoinColumn(nullable: false) ]
	private ?User $user = null;

    /**
     * @var Collection<int, Sequence>
     */
    #[ORM\OneToMany(targetEntity: Sequence::class, mappedBy: 'userEmailAccount')]
    private Collection $sequences;

    public function __construct()
    {
        $this->sequences = new ArrayCollection();
    }

	public function getId(): ?int {
		return $this->id;
	}

	public function getProvider(): ?string {
		return $this->provider;
	}

	public function setProvider( ?string $provider ): static {
		$this->provider = $provider;

		return $this;
	}

	public function getEmail(): ?string {
		return $this->email;
	}

	public function setEmail( ?string $email ): static {
		$this->email = $email;

		return $this;
	}

	public function getAccessToken(): ?string {
		return $this->access_token;
	}

	public function setAccessToken( string $access_token ): static {
		$this->access_token = $access_token;

		return $this;
	}

	public function getRefreshToken(): ?string {
		return $this->refresh_token;
	}

	public function setRefreshToken( ?string $refresh_token ): static {
		$this->refresh_token = $refresh_token;

		return $this;
	}

	public function getAccessTokenExpiry(): ?\DateTimeImmutable {
		return $this->access_token_expiry;
	}

	public function setAccessTokenExpiry( ?\DateTimeImmutable $access_token_expiry ): static {
		$this->access_token_expiry = $access_token_expiry;

		return $this;
	}

	public function getProviderUserId(): ?string {
		return $this->provider_user_id;
	}

	public function setProviderUserId( ?string $provider_user_id ): static {
		$this->provider_user_id = $provider_user_id;

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
    public function getSequences(): Collection
    {
        return $this->sequences;
    }

    public function addSequence(Sequence $sequence): static
    {
        if (!$this->sequences->contains($sequence)) {
            $this->sequences->add($sequence);
            $sequence->setUserEmailAccount($this);
        }

        return $this;
    }

    public function removeSequence(Sequence $sequence): static
    {
        if ($this->sequences->removeElement($sequence)) {
            // set the owning side to null (unless already changed)
            if ($sequence->getUserEmailAccount() === $this) {
                $sequence->setUserEmailAccount(null);
            }
        }

        return $this;
    }
}
