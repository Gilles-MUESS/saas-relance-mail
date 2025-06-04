<?php

namespace App\Command;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(name: 'emails:send-planned') ]
class SendPlannedEmailsCommand extends Command {
	private $em, $bus;

	public function __construct( EntityManagerInterface $em, MessageBusInterface $bus ) {
		parent::__construct();
		$this->em = $em;
		$this->bus = $bus;
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$now = new \DateTimeImmutable();
		$emails = $this->em->getRepository( Message::class)->findToSend( $now );

		foreach ( $emails as $message ) {
			$account = $message->getSequence()->getUserEmailAccount();
			$email = ( new Email() )
				->from( 'noreply@mailautomation.com' )
				->subject( $message->getSubject() )
				->text( $message->getMessage() );

			foreach ( $message->getSequence()->getRecipient() as $recipient ) {
				$email->to( $recipient->getEmail() );
			}

			// Ajout de pièces jointes si besoin
			// $email->attachFromPath('/chemin/vers/fichier.pdf');

			// Création du transport dynamique
			$dsn = sprintf( 'dynamic://default?provider=%s&email=%s&token=%s',
				$account->getProvider(),
				urlencode( $account->getEmail() ),
				urlencode( $account->getAccessToken() )
			);

			$this->bus->dispatch( new SendEmailMessage( $email ) );
			$message->setIsSent( true ); // ou suppression de la queue
		}
		$this->em->flush();
		return Command::SUCCESS;
	}
}
