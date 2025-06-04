<?php

namespace App\Mailer;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;

class DynamicTransportFactory extends AbstractTransportFactory {
	public function create( Dsn $dsn ): TransportInterface {
		// Exemple de DSN personnalisé : "dynamic://default?provider=google"
		$provider = $dsn->getOption( 'provider' );

		// Récupère les infos de connexion depuis UserEmailAccount (simulé ici)
		// $userEmailAccount = $this->getUserEmailAccount( $provider );

		// Génère le DSN réel en fonction du provider
		$realDsn = $this->generateDsnForProvider( $userEmailAccount );

		return Transport::fromDsn( $realDsn );
	}

	protected function getSupportedSchemes(): array {
		return [ 'dynamic' ]; // Supporte les DSN commençant par "dynamic://"
	}

	private function generateDsnForProvider( UserEmailAccount $account ): string {
		return match ( $account->getProvider() ) {
			'google' => sprintf(
				'gmail+smtp://%s:%s@default',
				urlencode( $account->getEmail() ),
				urlencode( $account->getAccessToken() )
			),
			'microsoft' => sprintf(
				'smtp://%s:%s@smtp.office365.com:587',
				$account->getEmail(),
				$account->getAccessToken()
			),
			default => throw new \InvalidArgumentException( 'Provider non supporté' )
		};
	}
}
