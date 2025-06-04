<?php

namespace App\Interfaces;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * Interface OAuthProviderInterface
 *
 * This interface defines the methods required for an OAuth provider integration.
 * It should be implemented by any class that handles OAuth authentication and user information retrieval.
 */
interface OAuthProviderInterface {
	const PROVIDER_GOOGLE = 'google';

	/**
	 * Get the authorization URL for the OAuth provider.
	 *
	 * @return string
	 */
	public function getAuthorizationUrl(): string;
	/**
	 * Get the access token from the OAuth provider.
	 *
	 * @param string $code The authorization code received from the provider.
	 * @return string The access token.
	 */
	public function getAccessToken( string $code ): AccessTokenInterface;

	/**
	 * Get the refresh token from the OAuth provider.
	 *
	 * @param  string $refreshToken
	 * @return string The updated access token.
	 */
	public function getRefreshToken( string $refreshToken ): AccessTokenInterface;

	/**
	 * Get user information from the OAuth provider using the access token.
	 *
	 * @param string $accessToken The access token.
	 * @return array An associative array containing user information.
	 */
	public function getUserInfo( string $accessToken ): array;
}
