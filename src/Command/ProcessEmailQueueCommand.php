<?php

namespace App\Command;

use App\Service\Email\EmailQueueManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour traiter la file d'attente des emails
 */
#[AsCommand(
    name: 'app:email:process-queue',
    description: 'Traite la file d\'attente des emails en attente d\'envoi'
)]
class ProcessEmailQueueCommand extends Command
{
    private const DEFAULT_BATCH_SIZE = 100;
    private const DEFAULT_MAX_RUNTIME = 300; // 5 minutes
    private const DEFAULT_MEMORY_LIMIT = 128; // MB

    private EmailQueueManager $queueManager;
    private ?LoggerInterface $logger;
    private int $batchSize;
    private int $maxRuntime;
    private int $memoryLimit;

    public function __construct(
        EmailQueueManager $queueManager,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct();
        $this->queueManager = $queueManager;
        $this->logger = $logger;

        // Initialize with default values, will be overridden in execute()
        $this->batchSize = self::DEFAULT_BATCH_SIZE;
        $this->maxRuntime = self::DEFAULT_MAX_RUNTIME;
        $this->memoryLimit = self::DEFAULT_MEMORY_LIMIT * 1024 * 1024;
    }

    private function getParameterValue(string $envName, $defaultValue): int
    {
        if (isset($_ENV[$envName])) {
            return (int)$_ENV[$envName];
        }
        return $defaultValue;
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Cette commande traite les emails en attente dans la file d\'attente.')
            ->addOption(
                'batch-size',
                'b',
                InputOption::VALUE_OPTIONAL,
                'Nombre maximum d\'emails à traiter',
                $this->getParameterValue('EMAIL_BATCH_SIZE', self::DEFAULT_BATCH_SIZE)
            )
            ->addOption(
                'max-runtime',
                't',
                InputOption::VALUE_OPTIONAL,
                'Durée maximale d\'exécution en secondes',
                $this->getParameterValue('EMAIL_MAX_RUNTIME', self::DEFAULT_MAX_RUNTIME)
            )
            ->addOption(
                'memory-limit',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Limite de mémoire en Mo',
                $this->getParameterValue('EMAIL_MEMORY_LIMIT_MB', self::DEFAULT_MEMORY_LIMIT)
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $startTime = time();

        // Récupération et validation des options
        $this->batchSize = (int) $input->getOption('batch-size');
        $this->maxRuntime = (int) $input->getOption('max-runtime');
        $this->memoryLimit = (int) $input->getOption('memory-limit') * 1024 * 1024;

        $io->title(sprintf(
            'Traitement de la file d\'attente des emails (lot de %d, durée max: %ds, mémoire: %dM)',
            $this->batchSize,
            $this->maxRuntime,
            $this->memoryLimit / (1024 * 1024)
        ));

        $processed = 0;
        $shouldContinue = true;

        while ($shouldContinue) {
            try {
                // Vérifier le temps d'exécution
                if ($this->isTimeLimitReached($startTime)) {
                    $io->note('Limite de temps atteinte');
                    break;
                }

                // Vérifier la mémoire
                if ($this->isMemoryLimitReached()) {
                    $io->warning('Limite de mémoire atteinte');
                    break;
                }

                // Traiter un lot d'emails
                $batchProcessed = $this->queueManager->processQueue($this->batchSize);

                if ($batchProcessed === 0) {
                    // Aucun email à traiter
                    $io->success('Aucun email à traiter');
                    $shouldContinue = false;
                } else {
                    $processed += $batchProcessed;
                    $io->writeln(sprintf(
                        'Lot traité: %d emails (total: %d)',
                        $batchProcessed,
                        $processed
                    ));
                }

                // Petite pause pour éviter une surcharge du CPU
                if ($shouldContinue) {
                    sleep(1);
                }

            } catch (\Exception $e) {
                $this->logError('Erreur lors du traitement de la file d\'attente', $e);
                $io->error(sprintf('Erreur: %s', $e->getMessage()));

                // En cas d'erreur, on fait une pause plus longue avant de réessayer
                sleep(10);
            }
        }

        $io->success(sprintf(
            'Traitement terminé. %d emails traités en %d secondes.',
            $processed,
            time() - $startTime
        ));

        return Command::SUCCESS;
    }

    /**
     * Vérifie si la limite de temps d'exécution est atteinte
     */
    private function isTimeLimitReached(int $startTime): bool
    {
        return (time() - $startTime) >= $this->maxRuntime;
    }

    /**
     * Vérifie si la limite de mémoire est atteinte
     */
    private function isMemoryLimitReached(): bool
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->memoryLimit * 0.9; // On s'arrête à 90% de la limite

        return $memoryUsage >= $memoryLimit;
    }

    /**
     * Journalise une erreur
     */
    private function logError(string $message, \Throwable $exception): void
    {
        if ($this->logger) {
            $this->logger->error($message, [
                'exception' => $exception,
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }
}
