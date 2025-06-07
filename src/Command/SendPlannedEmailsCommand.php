<?php

namespace App\Command;

use App\Entity\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;

#[AsCommand(name: 'emails:send-planned') ]
class SendPlannedEmailsCommand extends Command {
	public function __construct( private EntityManagerInterface $em, private MessageBusInterface $bus, private LoggerInterface $logger ) {
        parent::__construct();
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
        $userTimezone = new \DateTimeZone('Europe/Paris');
		$now = new \DateTimeImmutable('now', $userTimezone);
		$emails = $this->em->getRepository( Message::class)->findToSend( $now );

        $this->logger->info('Messages sélectionnés', [
            'ids' => array_map(fn($m) => $m->getId(), $emails)
        ]);

        if(!$emails) {
            $output->writeln('Aucun email trouvé à envoyer.');
            return Command::SUCCESS;
        }

		foreach ( $emails as $message ) {
            // Pour débugger
            $this->logger->info(sprintf(
                'ID: %d | Sujet: %s | Date: %s %s | isSent: %s',
                $message->getId(),
                $message->getSubject(),
                $message->getSendAt()?->format('Y-m-d') ?? 'null',
                $message->getSendAtTime()?->format('H:i:s') ?? 'null',
                $message->getIsSent() ? 'oui' : 'non'
            ));

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

			$this->bus->dispatch( new SendEmailMessage( $email ) );
			$message->setIsSent( true ); // ou suppression de la queue
		}
		$this->em->flush();
		return Command::SUCCESS;
	}
}
