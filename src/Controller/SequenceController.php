<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Sequence;
use App\Form\SequenceType;
use App\Form\RecipientType;
use App\Service\UserService;
use App\Form\SequenceLabelType;
use App\Repository\SequenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/app/sequence') ]
final class SequenceController extends AbstractController {
	public function __construct( private UserService $userService ) {
	}

	#[Route('/new', name: 'app_sequence_new', methods: [ 'GET', 'POST' ]) ]
	public function new( Request $request, EntityManagerInterface $entityManager ): Response {
		$user = $this->userService->getUser();

		$sequence = new Sequence();
		$sequence->addMessage( new Message() );

		$form = $this->createForm( SequenceType::class, $sequence );
		$form->handleRequest( $request );

		$recipientForm = $this->createForm( RecipientType::class);
		$labelForm = $this->createForm( SequenceLabelType::class);

		if ( $form->isSubmitted() && $form->isValid() ) {
			$sequence->setCreatedAt( new \DateTimeImmutable() );
			$sequence->setUser( $user );
			$sequence->setStatus( Sequence::STATUS_DRAFT );

			foreach ( $sequence->getMessages() as $message ) {
				$message->setSequence( $sequence );
				$message->setIsSent( false );
			}

			$entityManager->persist( $sequence );
			$entityManager->flush();

			$this->addFlash( 'success', 'La séquence a été créée avec succès.' );
			return $this->redirectToRoute( 'app_dashboard', [], Response::HTTP_SEE_OTHER );
		}

		return $this->render( 'sequence/new.html.twig', [ 
			'sequence' => $sequence,
			'form' => $form,
			'recipientForm' => $recipientForm,
			'labelForm' => $labelForm,
		] );
	}

	#[Route('/{id}', name: 'app_sequence_show', methods: [ 'GET' ]) ]
	public function show( Sequence $sequence ): Response {
		return $this->render( 'sequence/show.html.twig', [ 
			'sequence' => $sequence,
		] );
	}

	#[Route('/{id}/edit', name: 'app_sequence_edit', methods: [ 'GET', 'POST' ]) ]
	public function edit( Request $request, Sequence $sequence, EntityManagerInterface $entityManager ): Response {
		$user = $this->userService->getUser();

		$form = $this->createForm( SequenceType::class, $sequence );
		$form->handleRequest( $request );

		$recipientForm = $this->createForm( RecipientType::class);
		$labelForm = $this->createForm( SequenceLabelType::class);

		if ( $form->isSubmitted() && $form->isValid() ) {
			$sequence->setCreatedAt( new \DateTimeImmutable() );
			$sequence->setUser( $user );
			$sequence->setStatus( Sequence::STATUS_DRAFT );

			foreach ( $sequence->getMessages() as $message ) {
				$message->setSequence( $sequence );
				$message->setIsSent( false );
			}

			$entityManager->persist( $sequence );
			$entityManager->flush();

			$this->addFlash( 'success', 'La séquence a été créée avec succès.' );
			return $this->redirectToRoute( 'app_dashboard', [], Response::HTTP_SEE_OTHER );
		}

		return $this->render( 'sequence/edit.html.twig', [ 
			'sequence' => $sequence,
			'form' => $form,
			'recipientForm' => $recipientForm,
			'labelForm' => $labelForm,
		] );
	}

	#[Route('/ajax/delete/{id}', name: 'app_sequence_delete', methods: [ 'DELETE' ]) ]
	public function delete( Request $request, Sequence $sequence, EntityManagerInterface $entityManager ): JsonResponse {
		if ( $this->isCsrfTokenValid( 'delete_sequence', $request->headers->get( 'X-CSRF-Token' ) ) ) {
			try {
				$entityManager->remove( $sequence );
				$entityManager->flush();

				return $this->json( [ 
					'success' => true,
					'message' => 'La séquence a été supprimée avec succès.',
				] );
			} catch (\Exception $e) {
				return $this->json( [ 
					'success' => false,
					'message' => $e->getMessage(),
				], 400 );
			}
		}

		return $this->json( [ 
			'success' => false,
			'message' => 'La séquence n\'a pas pu être supprimée.',
		], 400 );
	}
}
