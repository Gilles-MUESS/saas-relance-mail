<?php

namespace App\Controller;

use App\Entity\SequenceLabel;
use App\Form\SequenceLabelType;
use App\Repository\SequenceLabelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sequence/label') ]
final class SequenceLabelController extends AbstractController {
	#[Route(name: 'app_sequence_label_index', methods: [ 'GET' ]) ]
	public function index( SequenceLabelRepository $sequenceLabelRepository ): Response {
		return $this->render( 'sequence_label/index.html.twig', [ 
			'sequence_labels' => $sequenceLabelRepository->findAll(),
		] );
	}

	#[Route('/ajax/new', name: 'app_ajax_sequence_label_new', methods: [ 'GET', 'POST' ]) ]
	public function new( Request $request, EntityManagerInterface $entityManager ): Response {
		$sequenceLabel = new SequenceLabel();
		$form = $this->createForm( SequenceLabelType::class, $sequenceLabel );
		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$sequenceLabel->addUser( $this->getUser() );
			$entityManager->persist( $sequenceLabel );
			$entityManager->flush();

			return $this->json( [ 
				'success' => true,
				'id' => $sequenceLabel->getId(),
				'text' => $sequenceLabel->getTitle(),
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

	#[Route('/{id}', name: 'app_sequence_label_show', methods: [ 'GET' ]) ]
	public function show( SequenceLabel $sequenceLabel ): Response {
		return $this->render( 'sequence_label/show.html.twig', [ 
			'sequence_label' => $sequenceLabel,
		] );
	}

	#[Route('/{id}/edit', name: 'app_sequence_label_edit', methods: [ 'GET', 'POST' ]) ]
	public function edit( Request $request, SequenceLabel $sequenceLabel, EntityManagerInterface $entityManager ): Response {
		$form = $this->createForm( SequenceLabelType::class, $sequenceLabel );
		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$entityManager->flush();

			return $this->redirectToRoute( 'app_sequence_label_index', [], Response::HTTP_SEE_OTHER );
		}

		return $this->render( 'sequence_label/edit.html.twig', [ 
			'sequence_label' => $sequenceLabel,
			'form' => $form,
		] );
	}

	#[Route('/{id}', name: 'app_sequence_label_delete', methods: [ 'POST' ]) ]
	public function delete( Request $request, SequenceLabel $sequenceLabel, EntityManagerInterface $entityManager ): Response {
		if ( $this->isCsrfTokenValid( 'delete' . $sequenceLabel->getId(), $request->getPayload()->getString( '_token' ) ) ) {
			$entityManager->remove( $sequenceLabel );
			$entityManager->flush();
		}

		return $this->redirectToRoute( 'app_sequence_label_index', [], Response::HTTP_SEE_OTHER );
	}
}
