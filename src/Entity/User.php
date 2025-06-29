<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class) ]
#[ORM\Table(name: '`user`') ]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: [ 'email' ]) ]
#[UniqueEntity(fields: [ 'email' ], message: 'There is already an account with this email') ]
class User implements UserInterface, PasswordAuthenticatedUserInterface {
	#[ORM\Id ]
	#[ORM\GeneratedValue ]
	#[ORM\Column ]
	private ?int $id = null;

	#[ORM\Column(length: 180) ]
	private ?string $email = null;

	/**
	 * @var list<string> The user roles
	 */
	#[ORM\Column ]
	private array $roles = [];

	/**
	 * @var string The hashed password
	 */
	#[ORM\Column ]
	private ?string $password = null;

	#[ORM\Column ]
	private ?\DateTimeImmutable $createdAt = null;

	/**
	 * @var Collection<int, Signature>
	 */
	#[ORM\OneToMany(targetEntity: Signature::class, mappedBy: 'user', orphanRemoval: true, cascade: [ 'remove' ]) ]
	private Collection $signatures;

	/**
	 * @var Collection<int, SequenceLabel>
	 */
	#[ORM\ManyToMany(targetEntity: SequenceLabel::class, mappedBy: 'user') ]
	private Collection $sequenceLabels;

	/**
	 * @var Collection<int, Sequence>
	 */
	#[ORM\OneToMany(targetEntity: Sequence::class, mappedBy: 'user', orphanRemoval: true, cascade: [ 'remove' ]) ]
	private Collection $sequences;

	/**
	 * @var Collection<int, Recipient>
	 */
	#[ORM\OneToMany(targetEntity: Recipient::class, mappedBy: 'user', orphanRemoval: true, cascade: [ 'remove' ]) ]
	private Collection $recipients;

	#[ORM\Column ]
	private bool $isVerified = false;

	#[ORM\OneToOne(mappedBy: 'user', cascade: [ 'persist', 'remove' ]) ]
	private ?UserInfo $userInfo = null;

	/**
	 * @var Collection<int, UserEmailAccount>
	 */
	#[ORM\OneToMany(targetEntity: UserEmailAccount::class, mappedBy: 'user', orphanRemoval: true) ]
	private Collection $userEmailAccounts;

	public function __construct() {
		$this->signatures = new ArrayCollection();
		$this->sequenceLabels = new ArrayCollection();
		$this->sequences = new ArrayCollection();
		$this->recipients = new ArrayCollection();
		$this->userEmailAccounts = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getEmail(): ?string {
		return $this->email;
	}

	public function setEmail( string $email ): static {
		$this->email = $email;

		return $this;
	}

	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUserIdentifier(): string {
		return (string) $this->email;
	}

	/**
	 * @see UserInterface
	 *
	 * @return list<string>
	 */
	public function getRoles(): array {
		$roles = $this->roles;
		// guarantee every user at least has ROLE_USER
		$roles[] = 'ROLE_USER';

		return array_unique( $roles );
	}

	/**
	 * @param list<string> $roles
	 */
	public function setRoles( array $roles ): static {
		$this->roles = $roles;

		return $this;
	}

	/**
	 * @see PasswordAuthenticatedUserInterface
	 */
	public function getPassword(): ?string {
		return $this->password;
	}

	public function setPassword( string $password ): static {
		$this->password = $password;

		return $this;
	}

	/**
	 * @see UserInterface
	 */
	public function eraseCredentials(): void {
		// If you store any temporary, sensitive data on the user, clear it here
		// $this->plainPassword = null;
	}

	public function getCreatedAt(): ?\DateTimeImmutable {
		return $this->createdAt;
	}

	public function setCreatedAt( \DateTimeImmutable $createdAt ): static {
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * @return Collection<int, Signature>
	 */
	public function getSignatures(): Collection {
		return $this->signatures;
	}

	public function addSignature( Signature $signature ): static {
		if ( ! $this->signatures->contains( $signature ) ) {
			$this->signatures->add( $signature );
			$signature->setUser( $this );
		}

		return $this;
	}

	public function removeSignature( Signature $signature ): static {
		if ( $this->signatures->removeElement( $signature ) ) {
			// set the owning side to null (unless already changed)
			if ( $signature->getUser() === $this ) {
				$signature->setUser( null );
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int, SequenceLabel>
	 */
	public function getSequenceLabels(): Collection {
		return $this->sequenceLabels;
	}

	public function addSequenceLabel( SequenceLabel $sequenceLabel ): static {
		if ( ! $this->sequenceLabels->contains( $sequenceLabel ) ) {
			$this->sequenceLabels->add( $sequenceLabel );
			$sequenceLabel->addUser( $this );
		}

		return $this;
	}

	public function removeSequenceLabel( SequenceLabel $sequenceLabel ): static {
		if ( $this->sequenceLabels->removeElement( $sequenceLabel ) ) {
			$sequenceLabel->removeUser( $this );
		}

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
			$sequence->setUser( $this );
		}

		return $this;
	}

	public function removeSequence( Sequence $sequence ): static {
		if ( $this->sequences->removeElement( $sequence ) ) {
			// set the owning side to null (unless already changed)
			if ( $sequence->getUser() === $this ) {
				$sequence->setUser( null );
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int, Recipient>
	 */
	public function getRecipients(): Collection {
		return $this->recipients;
	}

	public function addRecipient( Recipient $recipient ): static {
		if ( ! $this->recipients->contains( $recipient ) ) {
			$this->recipients->add( $recipient );
			$recipient->setUser( $this );
		}

		return $this;
	}

	public function removeRecipient( Recipient $recipient ): static {
		if ( $this->recipients->removeElement( $recipient ) ) {
			// set the owning side to null (unless already changed)
			if ( $recipient->getUser() === $this ) {
				$recipient->setUser( null );
			}
		}

		return $this;
	}

	public function isVerified(): bool {
		return $this->isVerified;
	}

	public function setIsVerified( bool $isVerified ): static {
		$this->isVerified = $isVerified;

		return $this;
	}

	public function getUserInfo(): ?UserInfo {
		return $this->userInfo;
	}

	public function setUserInfo( UserInfo $userInfo ): static {
		// set the owning side of the relation if necessary
		if ( $userInfo->getUser() !== $this ) {
			$userInfo->setUser( $this );
		}

		$this->userInfo = $userInfo;

		return $this;
	}

	/**
	 * @return Collection<int, UserEmailAccount>
	 */
	public function getUserEmailAccounts(): Collection {
		return $this->userEmailAccounts;
	}

	public function addUserEmailAccount( UserEmailAccount $userEmailAccount ): static {
		if ( ! $this->userEmailAccounts->contains( $userEmailAccount ) ) {
			$this->userEmailAccounts->add( $userEmailAccount );
			$userEmailAccount->setUser( $this );
		}

		return $this;
	}

	public function removeUserEmailAccount( UserEmailAccount $userEmailAccount ): static {
		if ( $this->userEmailAccounts->removeElement( $userEmailAccount ) ) {
			// set the owning side to null (unless already changed)
			if ( $userEmailAccount->getUser() === $this ) {
				$userEmailAccount->setUser( null );
			}
		}

		return $this;
	}

	public function getUserEmailAccountByProvider( string $provider ): Collection {
		$userEmailAccounts = $this->getUserEmailAccounts();

		return $userEmailAccounts->filter( function (UserEmailAccount $account) use ($provider) {
			return $account->getProvider() === $provider;
		} );
	}
}
