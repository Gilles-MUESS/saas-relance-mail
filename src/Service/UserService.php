<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class UserService {
	public function __construct( private Security $security ) {
	}

	/**
	 * Renvoie les initiales de l'utilisateur connecté.
	 *
	 * @return string
	 */
	public function getInitials(): ?string {
		/** @var User|null $user */
		$user = $this->security->getUser();

		if ( ! $user instanceof User ) {
			return null; // Aucun utilisateur connecté
		}

		// Get user initials
		$initials = '';
		if ( $user->getUserInfo()->getFirstName() && $user->getUserInfo()->getLastName() ) {
			$initials = strtoupper( substr( $user->getUserInfo()->getFirstName(), 0, 1 ) . substr( $user->getUserInfo()->getLastName(), 0, 1 ) );
		} elseif ( $user->getUserInfo()->getFirstName() ) {
			$initials = strtoupper( substr( $user->getUserInfo()->getFirstName(), 0, 1 ) );
		} elseif ( $user->getUserInfo()->getLastName() ) {
			$initials = strtoupper( substr( $user->getUserInfo()->getLastName(), 0, 1 ) );
		}

		return $initials ?: null;
	}

	public function getUser(): ?User {
		return $this->security->getUser();
	}
}