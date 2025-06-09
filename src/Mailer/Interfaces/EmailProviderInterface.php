<?php

namespace App\Mailer\Interfaces;

use App\Entity\Message;
use App\Entity\UserEmailAccount;

interface EmailProviderInterface
{
    /**
     * Envoie un email via le fournisseur
     *
     * @param Message $message Le message à envoyer
     * @param UserEmailAccount $account Le compte email à utiliser pour l'envoi
     * @return bool True si l'envoi a réussi, false sinon
     */
    public function send(Message $message, UserEmailAccount $account): bool;

    /**
     * Vérifie si le fournisseur supporte un type de compte donné
     *
     * @param string $providerType Le type de fournisseur (gmail, outlook, etc.)
     * @return bool
     */
    public function supports(string $providerType): bool;

    /**
     * Rafraîchit le token d'accès si nécessaire
     *
     * @param UserEmailAccount $account
     * @return bool True si le rafraîchissement a réussi
     */
    public function refreshTokenIfNeeded(UserEmailAccount $account): bool;
}
