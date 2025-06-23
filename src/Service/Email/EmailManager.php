<?php

namespace App\Service\Email;

use App\Entity\Message;
use App\Entity\UserEmailAccount;
use App\Mailer\Interfaces\EmailProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class EmailManager {
	private $providers;
	/**
	 * @param iterable<EmailProviderInterface> $providers
	 */
	public function __construct(
		#[AutowireIterator('app.email_provider') ] iterable $providers,
		private ?LoggerInterface $logger = null
	) {
		$this->providers = $providers;
	}

	/**
	 * Envoie un email en utilisant le fournisseur approprié
	 *
	 * @param Message $message Le message à envoyer
	 * @param UserEmailAccount $account Le compte à utiliser pour l'envoi
	 * @return bool True si l'envoi a réussi
	 */
	public function sendEmail( Message $message, UserEmailAccount $account ): bool {
		$this->log( 'debug', sprintf(
			'Recherche du provider pour le compte [%s] (%s)',
			$account->getId(),
			$account->getProvider()
		) );

		$provider = $this->getProviderForAccount( $account );

		if ( ! $provider ) {
			$this->log( 'error', sprintf(
				'No provider found for account type: %s',
				$account->getProvider()
			) );
			return false;
		}

		$this->log( 'debug', sprintf(
			'Provider [%s] trouvé pour le compte [%s]',
			get_class( $provider ),
			$account->getId()
		) );

		if ( ! method_exists( $provider, 'send' ) ) {
			$this->log( 'error', sprintf(
				'La méthode send() n\'existe pas sur le provider [%s]',
				get_class( $provider )
			) );
			return false;
		}

		$this->log( 'debug', sprintf(
			'Appel de send() sur le provider [%s] pour le compte [%s]',
			get_class( $provider ),
			$account->getId()
		) );

		return $provider->send( $message, $account );
	}

	/**
	 * Trouve le fournisseur adapté pour un compte donné
	 */
	private function getProviderForAccount( UserEmailAccount $account ): ?EmailProviderInterface {
		foreach ( $this->providers as $provider ) {
			if ( $provider->supports( $account->getProvider() ) ) {
				return $provider;
			}
		}

		return null;
	}

	/**
	 * Rafraîchit le token d'un compte si nécessaire
	 */
	public function refreshTokenIfNeeded( UserEmailAccount $account ): bool {
		$provider = $this->getProviderForAccount( $account );

		if ( ! $provider ) {
			return false;
		}

		return $provider->refreshTokenIfNeeded( $account );
	}

	/**
	 * Journalisation des événements
	 */
	private function log( string $level, string $message, array $context = [] ): void {
		if ( $this->logger ) {
			$this->logger->$level( $message, $context );
		}
	}
}
