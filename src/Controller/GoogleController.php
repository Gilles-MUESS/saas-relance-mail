<?php

namespace App\Controller;

use App\Entity\UserEmailAccount;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\Google;
use App\Interfaces\OAuthProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GoogleController extends AbstractController {

	#[Route('/app/connect/google', name: 'connect_google') ]
	public function connect( ClientRegistry $clientRegistry ): RedirectResponse {
		// Redirige vers Google pour l'authentification
		return $clientRegistry
			->getClient( 'google' )
			->redirect( [ 
				'profile',
				'email',
				'https://www.googleapis.com/auth/gmail.send',
				'https://www.googleapis.com/auth/gmail.readonly'
			],
				[ 
					'access_type' => 'offline',
					'prompt' => 'consent'
				] );
	}

	#[Route('/app/connect/google/check', name: 'oauth_check_google') ]
	public function connectCheck(
		Request $request,
		ClientRegistry $clientRegistry,
		EntityManagerInterface $em,
		Security $security
	): RedirectResponse {
		$client = $clientRegistry->getClient( 'google' );
		$accessToken = $client->getAccessToken();
		$user = $client->fetchUserFromToken( $accessToken );

		// Récupérer l'utilisateur connecté à l'application
		/** @var \App\Entity\User $appUser */
		$appUser = $security->getUser();

		// Créer ou mettre à jour le UserEmailAccount
		$userEmailAccount = new UserEmailAccount();
		$userEmailAccount->setProvider( 'google' );
		$userEmailAccount->setEmail( $user->getEmail() );
		$userEmailAccount->setAccessToken( $accessToken->getToken() );
		$userEmailAccount->setRefreshToken( $accessToken->getRefreshToken() );
		$userEmailAccount->setAccessTokenExpiry(
			( new \DateTimeImmutable() )->setTimestamp( $accessToken->getExpires() )
		);
		$userEmailAccount->setProviderUserId( $user->getId() );
		$userEmailAccount->setUser( $appUser );

		$em->persist( $userEmailAccount );
		$em->flush();

		$this->addFlash( 'success', 'Compte Google connecté !' );
		return $this->redirectToRoute( 'app_profile_index' );
	}
}
