<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile') ]
final class ProfileController extends AbstractController {
	public function __construct( private UserService $userService ) {
	}

	#[Route(name: 'app_profile_index', methods: [ 'GET' ]) ]
	public function index( UserRepository $userRepository ): Response {
		$user = $this->userService->getUser();
		$userInitials = $this->userService->getInitials();
		$userInfos = $user->getUserInfo();
		$userEmailAccount = $user->getUserEmailAccounts();

		return $this->render( 'profile/index.html.twig', [
			'user' => $user,
			'userInfos' => $userInfos,
			'userInitials' => $userInitials
		] );
	}

	#[Route('/new', name: 'app_profile_new', methods: [ 'GET', 'POST' ]) ]
	public function new( Request $request, EntityManagerInterface $entityManager ): Response {
		$user = new User();
		$form = $this->createForm( UserType::class, $user );
		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$entityManager->persist( $user );
			$entityManager->flush();

			return $this->redirectToRoute( 'app_profile_index', [], Response::HTTP_SEE_OTHER );
		}

		return $this->render( 'profile/new.html.twig', [
			'user' => $user,
			'form' => $form,
		] );
	}

	#[Route('/{id}', name: 'app_profile_show', methods: [ 'GET' ]) ]
	public function show( User $user ): Response {
		return $this->render( 'profile/show.html.twig', [
			'user' => $user,
		] );
	}

	#[Route('/{id}/edit', name: 'app_profile_edit', methods: [ 'GET', 'POST' ]) ]
	public function edit( Request $request, User $user, EntityManagerInterface $entityManager ): Response {
		$form = $this->createForm( UserType::class, $user );
		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$entityManager->flush();

			return $this->redirectToRoute( 'app_profile_index', [], Response::HTTP_SEE_OTHER );
		}

		return $this->render( 'profile/edit.html.twig', [
			'user' => $user,
			'form' => $form,
		] );
	}

	#[Route('/{id}', name: 'app_profile_delete', methods: [ 'POST' ]) ]
	public function delete( Request $request, User $user, EntityManagerInterface $entityManager ): Response {
		if ( $this->isCsrfTokenValid( 'delete' . $user->getId(), $request->getPayload()->getString( '_token' ) ) ) {
			$entityManager->remove( $user );
			$entityManager->flush();
		}

		return $this->redirectToRoute( 'app_profile_index', [], Response::HTTP_SEE_OTHER );
	}
}
