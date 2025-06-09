<?php

namespace App\Mailer\Provider;

use App\Entity\UserEmailAccount;
use App\Mailer\Interfaces\EmailProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class ProviderManager
{
    /**
     * @param iterable<EmailProviderInterface> $providers
     */
    public function __construct(
        #[AutowireIterator('app.email_provider')]
        private iterable $providers
    ) {
    }

    public function refreshTokenIfNeeded(UserEmailAccount $account): bool
    {
        $provider = $this->getProviderForAccount($account);
        if (!$provider) {
            return false;
        }

        return $provider->refreshTokenIfNeeded($account);
    }

    private function getProviderForAccount(UserEmailAccount $account): ?EmailProviderInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($account->getProvider())) {
                return $provider;
            }
        }

        return null;
    }
}
