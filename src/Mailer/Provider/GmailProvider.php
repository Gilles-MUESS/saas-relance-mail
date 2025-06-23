<?php

namespace App\Mailer\Provider;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureParam;

use App\Entity\UserEmailAccount;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;
use Symfony\Component\Mime\Part\TextPart;

/**
 * Fournisseur d'emails utilisant l'API Gmail
 */
class GmailProvider extends AbstractEmailProvider {
	private const PROVIDER_NAME = 'google';
	private const API_URL = 'https://gmail.googleapis.com/gmail/v1/users/me/messages/send';
	private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

	public function __construct(
		?\Psr\Log\LoggerInterface $logger = null,
		private string $clientId,
		private string $clientSecret
	) {
		parent::__construct( $logger );
	}

	/**
	 * {@inheritdoc}
	 */
	public function supports( string $providerType ): bool {
		return strtolower( $providerType ) === self::PROVIDER_NAME;
	}

	/**
	 * {@inheritdoc}
	 */
	public function refreshTokenIfNeeded( UserEmailAccount $account ): bool {
		$token = $account->getAccessToken();
		$expiresAt = $account->getAccessTokenExpiry();

		// Si le token est toujours valide, pas besoin de rafraîchir
		if ( $expiresAt && $expiresAt > new \DateTime( '+5 minutes' ) ) {
			return true;
		}

		try {
			$response = $this->httpClient->request( 'POST', self::TOKEN_URL, [ 
				'body' => http_build_query( [ 
					'client_id' => $this->clientId,
					'client_secret' => $this->clientSecret,
					'refresh_token' => $account->getRefreshToken(),
					'grant_type' => 'refresh_token',
				] ),
				'headers' => [ 
					'Content-Type' => 'application/x-www-form-urlencoded',
				],
			] );

			$data = $response->toArray();

			// Mettre à jour les tokens dans le compte
			$account->setAccessToken( $data['access_token'] );
			$account->setAccessTokenExpiry( new \DateTimeImmutable( '+' . ( $data['expires_in'] ?? 3600 ) . ' seconds' ) );

			return true;
		} catch (\Exception $e) {
			$this->log( 'error', 'Failed to refresh token: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doSend( Email $email, UserEmailAccount $account ): bool {
		$this->log( 'debug', sprintf(
			'Début doSend pour le compte [%s] (provider: %s)',
			$account->getId(),
			$account->getProvider()
		) );

		try {
			$this->log( 'debug', 'Création du message Gmail' );
			$message = $this->createGmailMessage( $email );

			$this->log( 'debug', 'Envoi de la requête HTTP à Gmail API' );
			$response = $this->httpClient->request( 'POST', self::API_URL, [ 
				'auth_bearer' => $account->getAccessToken(),
				'json' => [ 
					'raw' => $message,
				],
			] );

			$statusCode = $response->getStatusCode();
			$this->log( 'debug', 'Réponse Gmail API status: ' . $statusCode );

			$content = $response->getContent( false ); // false pour ne pas lever d'exception sur erreur HTTP
			$this->log( 'debug', 'Contenu réponse Gmail API: ' . $content );

			return $statusCode === 200;
		} catch (\Exception $e) {
			$this->log( 'error', 'Failed to send email: ' . $e->getMessage(), [ 
				'exception' => $e,
				'trace' => $e->getTraceAsString(),
			] );
			return false;
		}
	}

	/**
	 * Crée un message au format Gmail
	 */
	private function createGmailMessage( Email $email ): string {
		$headers = $email->getHeaders();
		$headers->addTextHeader( 'MIME-Version', '1.0' );
		$headers->addTextHeader( 'X-Mailer', 'PHP/' . PHP_VERSION );

		// Construire le message selon le contenu disponible
		if ( $email->getHtmlBody() !== null && $email->getTextBody() !== null ) {
			// Les deux versions texte et HTML
			$part1 = new TextPart( $email->getTextBody(), 'utf-8', 'plain', '8bit' );
			$part2 = new TextPart( $email->getHtmlBody(), 'utf-8', 'html', '8bit' );
			$email->setBody( new AlternativePart( $part1, $part2 ) );
		} elseif ( $email->getTextBody() !== null ) {
			// Version texte uniquement
			$email->text( $email->getTextBody() );
		} elseif ( $email->getHtmlBody() !== null ) {
			// Version HTML uniquement
			$email->html( $email->getHtmlBody() );
		}

		// TODO Gestion des pièces jointes
		// Ajouter les pièces jointes
		// foreach ($email->getAttachments() as $attachment) {
		//     $email->addPart(DataPart::fromPath(
		//         $attachment->getPath(),
		//         $attachment->getFilename(),
		//         $attachment->getContentType()
		//     ));
		// }

		// Convertir en chaîne et encoder en base64
		$message = $email->toString();
		return rtrim( strtr( base64_encode( $message ), [ '+' => '-', '/' => '_' ] ), '=' );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getRateLimit(): int {
		// Gmail a une limite de 2000 emails par jour, soit environ 83 par heure
		return 80; // On prend une marge de sécurité
	}
}
