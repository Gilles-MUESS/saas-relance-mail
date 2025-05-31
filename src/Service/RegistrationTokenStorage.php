<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class RegistrationTokenStorage {
	private CacheInterface $cache;
	private int $ttl;

	public function __construct( CacheInterface $cache, int $ttl = 1800 ) {
		$this->cache = $cache;
		$this->ttl = $ttl; // durée de vie du token en secondes (10 min par défaut)
	}

	public function save( string $token, array $data ): void {
		// $this->cache->delete( $token ); // Précaution pour un token one shot
		$this->cache->get( $token, function (ItemInterface $item) use ($data) {
			$item->expiresAfter( $this->ttl );
			return $data;
		} );
	}

	public function get( string $token ): ?array {
		return $this->cache->get( $token, function (ItemInterface $item) {
			// Si le token n'existe pas, retourne null
			return null;
		} );
	}

	public function delete( string $token ): void {
		$this->cache->delete( $token );
	}
}