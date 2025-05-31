<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserInfo;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use Symfony\Component\Mime\Address;
use App\Security\AppFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\RegistrationTokenStorage;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController {
	public function __construct( private EmailVerifier $emailVerifier, private RegistrationTokenStorage $tokenStorage ) {
	}

	#[Route('/register', name: 'app_register') ]
	/**
	 * Registration page.
	 *
	 * @param  Request $request
	 * @param  UserPasswordHasherInterface $userPasswordHasher
	 * @param  Security $security
	 * @param  EntityManagerInterface $entityManager
	 * @return Response
	 */
	public function register( Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager ): Response {
		$user = new User();
		$userInfo = new UserInfo();

		$form = $this->createForm( RegistrationFormType::class, $user );
		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			/** @var string $plainPassword */

			$userInfo->setFirstName( $form->get( 'userInfo' )->get( 'firstName' )->getData() );
			$userInfo->setLastName( $form->get( 'userInfo' )->get( 'lastName' )->getData() );
			$userInfo->setCompany( $form->get( 'userInfo' )->get( 'company' )->getData() );
			$userInfo->setAddress( $form->get( 'userInfo' )->get( 'address' )->getData() . ' ' . $form->get( 'userInfo' )->get( 'address2' )->getData() );
			$userInfo->setCp( $form->get( 'userInfo' )->get( 'cp' )->getData() );
			$userInfo->setCity( $form->get( 'userInfo' )->get( 'city' )->getData() );
			$user->setUserInfo( $userInfo );
			$user->setCreatedAt( new \DateTimeImmutable() );
			$user->setRoles( [ 'ROLE_USER' ] );
			$user->setEmail( $form->get( 'email' )->getData() );

			$plainPassword = $form->get( 'plainPassword' )->getData();

			// encode the plain password
			$user->setPassword( $userPasswordHasher->hashPassword( $user, $plainPassword ) );

			$entityManager->persist( $user );
			$entityManager->flush();

			// generate a signed url and email it to the user
			$this->emailVerifier->sendEmailConfirmation( 'app_verify_email', $user,
				( new TemplatedEmail() )
					->from( new Address( 'gillesmuess.pro@gmail.com', 'Relance Automatique' ) )
					->to( (string) $user->getEmail() )
					->subject( 'Please Confirm your Email' )
					->htmlTemplate( 'registration/confirmation_email.html.twig' )
			);

			// return $security->login( $user, AppFormAuthenticator::class, 'main' );
			// Génère un token sécurisé
			$token = bin2hex( random_bytes( 32 ) );
			// Stocke les données associées au token
			$this->tokenStorage->save( $token, [ 
				'email' => $user->getEmail(),
				'firstName' => $user->getUserInfo()->getFirstName(),
			] );

			// Redirige avec le token uniquement
			return $this->redirectToRoute( 'app_register_confirmation', [ 
				'token' => $token,
			] );
		}

		return $this->render( 'registration/register.html.twig', [ 
			'registrationForm' => $form,
		] );
	}

	#[Route('/verify/email', name: 'app_verify_email') ]
	/**
	 * Email verification after registrationn.
	 *
	 * @param  Request $request
	 * @param  TranslatorInterface $translator
	 * @param  Security $security
	 * @return Response
	 */
	public function verifyUserEmail( Request $request, TranslatorInterface $translator, Security $security ): Response {
		$this->denyAccessUnlessGranted( 'IS_AUTHENTICATED_FULLY' );

		// validate email confirmation link, sets User::isVerified=true and persists
		try {
			/** @var User $user */
			$user = $this->getUser();
			$this->emailVerifier->handleEmailConfirmation( $request, $user );
			$security->login( $user, AppFormAuthenticator::class, 'main' );
		} catch (VerifyEmailExceptionInterface $exception) {
			$this->addFlash( 'verify_email_error', $translator->trans( $exception->getReason(), [], 'VerifyEmailBundle' ) );

			return $this->redirectToRoute( 'app_register' );
		}

		$this->addFlash( 'success', 'Your email address has been verified.' );
		return $this->redirectToRoute( 'app_dashboard' );
	}


	#[Route('/register/confirmation', name: 'app_register_confirmation') ]
	/**
	 * Confirmationn page after registration.
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function confirm( Request $request ): Response {
		if ( $this->isGranted( 'IS_AUTHENTICATED_FULLY' ) ) {
			return $this->redirectToRoute( 'app_dashboard' );
		}

		$token = $request->query->get( 'token' );
		if ( ! $token ) {
			throw $this->createNotFoundException( 'Token manquant.' );
		}

		$data = $this->tokenStorage->get( $token );
		if ( ! $data ) {
			throw $this->createNotFoundException( 'Lien de confirmation invalide ou expiré.' );
		}

		return $this->render( 'registration/confirmation_register.html.twig', [ 
			'email' => $data['email'],
			'firstName' => $data['firstName'],
		] );
	}

	#[Route('/register/resend-confirmation', name: 'app_register_resend_confirmation', methods: [ 'POST' ]) ]
	/**
	 * AJAX method to resend confirmation email.
	 *
	 * @param  Request $request
	 * @param  EntityManagerInterface $entityManager
	 * @return Response
	 */
	public function resendConfirmationEmail( Request $request, EntityManagerInterface $entityManager ): Response {
		$token = $request->request->get( '_csrf_token' );

		if ( ! $token ) {
			throw $this->createNotFoundException( 'Token manquant.' );
		}

		$email = $request->request->get( 'email' );
		$user = $entityManager->getRepository( User::class)->findOneBy( [ 'email' => $email ] );

		if ( ! $user ) {
			return $this->json( [ 'status' => 'error', 'message' => 'Utilisateur introuvable.' ], 404 );
		}

		if ( $user->isVerified() ) {
			return $this->json( [ 'status' => 'info', 'message' => 'Votre compte est déjà vérifié.' ], 200 );
		}

		$this->emailVerifier->sendEmailConfirmation( 'app_verify_email', $user,
			( new TemplatedEmail() )
				->from( new Address( 'gillesmuess.pro@gmail.com', 'Relance Automatique' ) )
				->to( (string) $user->getEmail() )
				->subject( 'Please Confirm your Email' )
				->htmlTemplate( 'registration/confirmation_email.html.twig' )
		);

		return $this->json( [ 'status' => 'success', 'message' => 'Un nouvel email de confirmation vous a été envoyé.' ] );
	}
}
