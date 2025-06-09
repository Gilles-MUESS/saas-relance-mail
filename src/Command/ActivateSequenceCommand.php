<?php

namespace App\Command;

use App\Entity\Sequence;
use App\Repository\SequenceRepository;
use App\Service\SequenceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sequence:activate',
    description: 'Active une séquence et ajoute ses messages à la file d\'attente',
)]
class ActivateSequenceCommand extends Command
{
    public function __construct(
        private SequenceRepository $sequenceRepository,
        private SequenceService $sequenceService,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('sequence_id', InputArgument::REQUIRED, 'ID de la séquence à activer')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $sequenceId = $input->getArgument('sequence_id');

        $sequence = $this->sequenceRepository->find($sequenceId);
        
        if (!$sequence) {
            $io->error(sprintf('Séquence avec l\'ID %s non trouvée', $sequenceId));
            return Command::FAILURE;
        }

        if ($sequence->getStatus() === Sequence::STATUS_ACTIVE) {
            $io->warning('La séquence est déjà active');
            return Command::SUCCESS;
        }

        try {
            $io->text(sprintf('Activation de la séquence : %s', $sequence->getName()));
            
            // Activer la séquence
            $this->sequenceService->activateSequence($sequence);
            
            // Mettre à jour le statut
            $sequence->setStatus(Sequence::STATUS_ACTIVE);
            $this->entityManager->flush();
            
            $io->success('Séquence activée avec succès');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error(sprintf('Erreur lors de l\'activation de la séquence : %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
