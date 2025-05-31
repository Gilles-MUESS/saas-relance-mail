<?php

namespace App\Controller;

use App\Entity\Recipient;
use App\Form\RecipientType;
use App\Repository\RecipientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/recipient') ]
final class RecipientController extends AbstractController {
	#[Route(name: 'app_recipient_index', methods: [ 'GET' ]) ]
	public function index( RecipientRepository $recipientRepository ): Response {
		return $this->render( 'recipient/index.html.twig', [ 
			'recipients' => $recipientRepository->findAll(),
		] );
	}

	/**
	 * AJAX route to create a new recipient.
	 */
	#[Route('/ajax/new', name: 'app_ajax_recipient_new', methods: [ 'GET', 'POST' ]) ]
	public function new( Request $request, EntityManagerInterface $entityManager ): JsonResponse {
		$recipient = new Recipient();
		$form = $this->createForm( RecipientType::class, $recipient );
		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$recipient->setUser( $this->getUser() );
			$entityManager->persist( $recipient );
			$entityManager->flush();

			return $this->json( [ 
				'success' => true,
				'id' => $recipient->getId(),
				'text' => $recipient->getFirstName() . $recipient->getLastName() . ' <' . $recipient->getEmail() . '>',
			] );
		}

		// En cas d'erreur, renvoyer les erreurs du formulaire
		$errors = [];
		foreach ( $form->getErrors( true ) as $error ) {
			$errors[] = $error->getMessage();
		}

		return $this->json( [ 
			'success' => false,
			'errors' => $errors,
		], 400 );
	}

	#[Route('/{id}', name: 'app_recipient_show', methods: [ 'GET' ]) ]
	public function show( Recipient $recipient ): Response {
		return $this->render( 'recipient/show.html.twig', [ 
			'recipient' => $recipient,
		] );
	}

	#[Route('/{id}/edit', name: 'app_recipient_edit', methods: [ 'GET', 'POST' ]) ]
	public function edit( Request $request, Recipient $recipient, EntityManagerInterface $entityManager ): Response {
		$form = $this->createForm( RecipientType::class, $recipient );
		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$entityManager->flush();

			return $this->redirectToRoute( 'app_recipient_index', [], Response::HTTP_SEE_OTHER );
		}

		return $this->render( 'recipient/edit.html.twig', [ 
			'recipient' => $recipient,
			'form' => $form,
		] );
	}

	#[Route('/{id}', name: 'app_recipient_delete', methods: [ 'POST' ]) ]
	public function delete( Request $request, Recipient $recipient, EntityManagerInterface $entityManager ): Response {
		if ( $this->isCsrfTokenValid( 'delete' . $recipient->getId(), $request->getPayload()->getString( '_token' ) ) ) {
			$entityManager->remove( $recipient );
			$entityManager->flush();
		}

		return $this->redirectToRoute( 'app_recipient_index', [], Response::HTTP_SEE_OTHER );
	}
}
