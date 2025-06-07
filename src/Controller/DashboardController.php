<?php

namespace App\Controller;

use App\Entity\Sequence;
use App\Service\UserService;
use App\Service\SequenceService;
use App\Repository\SequenceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * DashboardController
 */
final class DashboardController extends AbstractController {

	public function __construct(
		private SequenceRepository $sequenceRepository,
		private UserService $userService,
		private SequenceService $sequenceService
	) {
	}
	/**
	 * index
	 * Displays the dashboard page.
	 *
	 * @return Response
	 */
	#[Route('/app/dashboard', name: 'app_dashboard') ]
	public function index(): Response {
		// Get current user
		$user = $this->getUser();

		if ( ! $user ) {
			return $this->redirectToRoute( 'app_login' );
		}

		// Fetch the sequences from the repository
		$sequences = $this->sequenceRepository->findBy( [ 'user' => $user->getId() ] );
		$sequencesActive = array_merge( $this->sequenceService->getSequencesByStatus( $sequences, Sequence::STATUS_ACTIVE ), $this->sequenceService->getSequencesByStatus( $sequences, Sequence::STATUS_DRAFT ) );
		$sequencesFail = $this->sequenceService->getSequencesByStatus( $sequences, Sequence::STATUS_FAIL );
		$sequencesSuccess = $this->sequenceService->getSequencesByStatus( $sequences, Sequence::STATUS_SUCCESS );

		$nextRelances = [];
		foreach ( $sequencesActive as $sequence ) {
			$nextRelances[ $sequence->getId()] = $this->sequenceService->getNextMessageDate( $sequence );
		}

		return $this->render( 'dashboard/index.html.twig', [
			'sequencesActive' => $sequencesActive,
			'sequencesFail' => $sequencesFail,
			'sequencesSuccess' => $sequencesSuccess,
			'nextRelances' => $nextRelances
		] );
	}
}
