<?php

namespace App\Mailer\Provider;

use App\Entity\Message;
use App\Entity\UserEmailAccount;
use App\Mailer\Interfaces\EmailProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Classe de base abstraite pour les fournisseurs d'emails
 */
abstract class AbstractEmailProvider implements EmailProviderInterface {
	protected HttpClientInterface $httpClient;
	protected ?LoggerInterface $logger;
	protected string $providerName;
	protected array $rateLimits = [];

	public function __construct( ?LoggerInterface $logger = null ) {
		$this->httpClient = HttpClient::create();
		$this->logger = $logger;
		$this->providerName = static::class; // Pour éviter une variable vide
		if ( $this->logger ) {
			$this->logger->info( '[DEBUG] Logger is injected in AbstractEmailProvider' );
		} else {
			error_log( '[DEBUG] Logger is NOT injected in AbstractEmailProvider' );
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function send( Message $message, UserEmailAccount $account ): bool {
		error_log( '[DEBUG] send() called in AbstractEmailProvider' );

		try {
			$this->log( 'debug', 'Début send()' );

			// Vérifier les limites de taux avant l'envoi
			if ( ! $this->checkRateLimit( $account ) ) {
				$this->log( 'warning', 'Rate limit reached for account ' . $account->getEmail() );
				return false;
			}
			$this->log( 'debug', 'Rate limit OK' );

			// Rafraîchir le token si nécessaire
			if ( ! $this->refreshTokenIfNeeded( $account ) ) {
				$this->log( 'error', 'Failed to refresh token for account ' . $account->getEmail() );
				return false;
			}
			$this->log( 'debug', 'Token OK' );

			// Construire et envoyer l'email
			$this->log( 'debug', 'Construction de l\'objet Email' );
			$email = $this->buildEmail( $message, $account );

			$this->log( 'debug', 'Appel de doSend()' );
			$result = $this->doSend( $email, $account );

			if ( $result ) {
				$this->updateRateLimit( $account );
				$this->log( 'info', sprintf( 'Email sent successfully from %s to %s',
					$account->getEmail(),
					implode( ', ', $message->getSequence()->getRecipient()->toArray() )
				) );
			} else {
				$this->log( 'error', 'doSend() a retourné false' );
			}

			return $result;
		} catch (\Exception $e) {
			$this->log( 'error', sprintf(
				'Error sending email: %s',
				$e->getMessage()
			), [ 'exception' => $e ] );
			return false;
		}
	}

	/**
	 * Vérifie si le fournisseur est disponible
	 */
	protected function isProviderAvailable(): bool {
		// Implémentez une vérification de disponibilité si nécessaire
		return true;
	}

	/**
	 * Construit un objet Email à partir d'un EmailMessage
	 */
	protected function buildEmail( Message $message, UserEmailAccount $account ): Email {
		$email = ( new Email() )
			->from( $account->getEmail() )
			->subject( $message->getSubject() )
			->text( $message->getMessage() );
		foreach ( $message->getSequence()->getRecipient()->toArray() as $recipient ) {
			$email->to( new Address( $recipient->getEmail(), $recipient->getFirstName() . ' ' . $recipient->getLastName() ) );
		}

		// Ajouter les pièces jointes
		foreach ( $message->getAttachment() as $attachment ) {
			$email->addPart( new DataPart(
				$attachment['content'],
				$attachment['filename'] ?? null,
				$attachment['contentType'] ?? null
			) );
		}

		return $email;
	}

	/**
	 * Méthode abstraite pour l'envoi effectif de l'email
	 */
	abstract protected function doSend( Email $email, UserEmailAccount $account ): bool;

	/**
	 * Vérifie les limites de taux
	 */
	protected function checkRateLimit( UserEmailAccount $account ): bool {
		$accountId = $account->getId();
		$now = time();

		// Réinitialiser le compteur si nécessaire (par exemple, toutes les heures)
		if ( isset( $this->rateLimits[ $accountId ]['reset_time'] ) &&
			$this->rateLimits[ $accountId ]['reset_time'] < $now ) {
			unset( $this->rateLimits[ $accountId ] );
		}

		// Vérifier la limite (par exemple, 100 emails/heure)
		$maxEmailsPerHour = $this->getRateLimit();
		if ( isset( $this->rateLimits[ $accountId ]['count'] ) &&
			$this->rateLimits[ $accountId ]['count'] >= $maxEmailsPerHour ) {
			return false;
		}

		return true;
	}

	/**
	 * Retourne la limite de taux pour ce fournisseur
	 */
	protected function getRateLimit(): int {
		// Valeur par défaut, à surcharger dans les classes filles
		return 100; // 100 emails par heure
	}

	/**
	 * Met à jour le compteur de taux
	 */
	protected function updateRateLimit( UserEmailAccount $account ): void {
		$accountId = $account->getId();

		if ( ! isset( $this->rateLimits[ $accountId ] ) ) {
			$this->rateLimits[ $accountId ] = [ 
				'count' => 0,
				'reset_time' => strtotime( '+1 hour' )
			];
		}

		$this->rateLimits[ $accountId ]['count']++;
	}

	/**
	 * Journalisation des événements
	 */
	protected function log( string $level, string $message, array $context = [] ): void {
		if ( $this->logger ) {
			$this->logger->$level( "[{$this->providerName}] $message", $context );
		}
	}
}
