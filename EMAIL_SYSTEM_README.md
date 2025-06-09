# Système d'Envoi d'Emails avec File d'Attente

Ce système fournit une solution complète pour gérer l'envoi d'emails via différents fournisseurs (Gmail, Outlook, etc.) avec une file d'attente robuste et un traitement asynchrone.

## Architecture

### Composants Principaux

1. **Fournisseurs d'Emails**
   - Interface commune `EmailProviderInterface`
   - Classe de base `AbstractEmailProvider`
   - Implémentation pour Gmail `GmailProvider`

2. **Gestion de la File d'Attente**
   - Entité `QueuedEmail` pour suivre les emails
   - Service `EmailQueueManager` pour gérer la file
   - Traitement asynchrone avec Symfony Messenger

3. **Commandes**
   - `app:email:process-queue` pour traiter la file d'attente

## Configuration Requise

1. Copiez `.env.local.example` vers `.env.local` et configurez les variables :
   ```bash
   cp .env.local.example .env.local
   ```

2. Configurez les variables d'environnement dans `.env.local` :
   - Identifiants OAuth pour chaque fournisseur
   - Paramètres de la file d'attente
   - Configuration de la base de données

3. Installez les dépendances :
   ```bash
   composer require symfony/messenger symfony/mailer symfony/http-client
   ```

4. Exécutez les migrations pour créer les tables nécessaires :
   ```bash
   php bin/console doctrine:migrations:diff
   php bin/console doctrine:migrations:migrate
   ```

## Utilisation

### 1. Enregistrement d'un Compte Email

```php
// Exemple d'enregistrement d'un compte Gmail
$userEmailAccount = new UserEmailAccount();
$userEmailAccount->setEmail('votre@email.com');
$userEmailAccount->setProviderType('gmail');
$userEmailAccount->setAccessToken('votre_access_token');
$userEmailAccount->setRefreshToken('votre_refresh_token');
$userEmailAccount->setTokenExpiresAt(new \DateTime('+1 hour'));
$userEmailAccount->setActive(true);

$entityManager->persist($userEmailAccount);
$entityManager->flush();
```

### 2. Création et Envoi d'un Email

```php
// Création d'un message
$emailMessage = new EmailMessage();
$emailMessage->setTo(['destinataire@example.com' => 'Nom du Destinataire']);
$emailMessage->setSubject('Sujet de l\'email');
$emailMessage->setTextBody('Contenu texte de l\'email');
$emailMessage->setHtmlBody('<p>Contenu HTML de l\'email</p>');

// Ajout de pièces jointes (optionnel)
$emailMessage->addAttachment([
    'content' => file_get_contents('/chemin/vers/fichier.pdf'),
    'filename' => 'document.pdf',
    'contentType' => 'application/pdf'
]);

// Ajout à la file d'attente pour envoi immédiat
$emailQueueManager->queueEmail($emailMessage, $userEmailAccount);

// Ou pour un envoi différé
$sendAt = new \DateTime('tomorrow 09:00');
$emailQueueManager->queueEmail($emailMessage, $userEmailAccount, $sendAt);
```

### 3. Traitement de la File d'Attente

Pour traiter manuellement la file d'attente :

```bash
# Traiter jusqu'à 100 emails (par défaut)
php bin/console app:email:process-queue

# Spécifier un nombre personnalisé d'emails
php bin/console app:email:process-queue --batch-size=50

# Limiter le temps d'exécution à 10 minutes
php bin/console app:email:process-queue --max-runtime=600
```

### 4. Configuration du Cron

Pour un traitement automatique, ajoutez cette ligne à votre crontab :

```bash
# Toutes les 5 minutes, traiter la file d'attente
*/5 * * * * cd /chemin/vers/votre/projet && php bin/console app:email:process-queue --max-runtime=240 >> /var/log/email-queue.log 2>&1
```

## Gestion des Erreurs

- Les échecs d'envoi sont automatiquement réessayés (3 tentatives par défaut)
- Les erreurs sont journalisées dans les logs de l'application
- Vous pouvez surveiller les échecs avec la commande :
  ```bash
  php bin/console doctrine:query:sql "SELECT COUNT(*) FROM queued_email WHERE status = 'failed'"
  ```

## Extensibilité

### Ajouter un Nouveau Fournisseur

1. Créez une nouvelle classe qui étend `AbstractEmailProvider`
2. Implémentez les méthodes requises
3. Ajoutez le service dans `config/services/email.yaml`

Exemple pour un fournisseur Outlook :

```yaml
# config/services/email.yaml
App\Mailer\Provider\OutlookProvider:
    arguments:
        $clientId: '%env(OUTLOOK_CLIENT_ID)%'
        $clientSecret: '%env(OUTLOOK_CLIENT_SECRET)%'
    tags: ['app.email_provider']
```

## Bonnes Pratiques

1. **Gestion des Tokens** : Assurez-vous de rafraîchir les tokens avant leur expiration
2. **Limites d'Envoi** : Respectez les limites d'envoi des fournisseurs
3. **Surveillance** : Surveillez la taille de la file d'attente et les échecs
4. **Sécurité** : Stockez les tokens de manière sécurisée
5. **Performances** : Ajustez la taille des lots en fonction de votre charge serveur

## Dépannage

### Problèmes Courants

1. **Échec d'authentification** : Vérifiez que les tokens sont valides et non expirés
2. **Limites de dépassement** : Réduisez la taille des lots ou augmentez les intervalles entre les envois
3. **Problèmes de mémoire** : Réduisez la taille des lots ou augmentez `EMAIL_MEMORY_LIMIT_MB`

### Logs

Consultez les logs pour plus d'informations sur les erreurs :

```bash
tail -f var/log/prod.log | grep email
```

## Sécurité

- Ne stockez jamais les tokens en clair dans le code
- Utilisez toujours HTTPS pour les appels API
- Limitez les autorisations des tokens OAuth au strict nécessaire
- Mettez à jour régulièrement les dépendances pour corriger les failles de sécurité

## Licence

Ce projet est sous licence [votre licence ici].
