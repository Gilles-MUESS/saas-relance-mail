<?php

// src/OAuth/GoogleOAuthProvider.php

namespace App\OAuth;

use App\Interfaces\OAuthProviderInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Token\AccessTokenInterface;

class GoogleOAuthProvider implements OAuthProviderInterface {
	private ClientRegistry $clientRegistry;

	public function __construct( ClientRegistry $clientRegistry ) {
		$this->clientRegistry = $clientRegistry;
	}

	public function getAuthorizationUrl(): string {
		$client = $this->clientRegistry->getClient( 'google' );
		// getAuthorizationUrl() n’existe pas directement, il faut utiliser redirect() dans le contrôleur
		// Ici, tu peux retourner l’URL si besoin (pour AJAX, etc.)
		return $client->getOAuth2Provider()->getAuthorizationUrl( [ 
			'scope' => [ 'profile', 'email', 'https://www.googleapis.com/auth/gmail.send', 'https://www.googleapis.com/auth/gmail.readonly' ],
			'access_type' => 'offline',
			'prompt' => 'consent'
		] );
	}

	public function getAccessToken( string $code ): AccessTokenInterface {
		$client = $this->clientRegistry->getClient( 'google' );
		return $client->getOAuth2Provider()->getAccessToken( 'authorization_code', [ 
			'code' => $code,
		] );
	}

	public function getRefreshToken( string $refreshToken ): AccessTokenInterface {
		$client = $this->clientRegistry->getClient( 'google' );
		return $client->getOAuth2Provider()->getAccessToken( 'refresh_token', [ 
			'refresh_token' => $refreshToken,
		] );
	}

	public function getUserInfo( string $accessToken ): array {
		$client = $this->clientRegistry->getClient( 'google' );
		$ownerDetails = $client->getOAuth2Provider()->getResourceOwner( new \League\OAuth2\Client\Token\AccessToken( [ 'access_token' => $accessToken ] ) );
		return [ 
			'email' => $ownerDetails->getEmail(),
			'id' => $ownerDetails->getId(),
			'name' => $ownerDetails->getName(),
		];
	}
}
