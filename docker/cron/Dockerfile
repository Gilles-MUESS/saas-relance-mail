FROM php:8.4-cli

# Installer cron
RUN apt-get update && apt-get install -y cron vim \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /app

# Copier le fichier de cron
COPY cronjobs /etc/cron.d/cronjobs
RUN chmod 0644 /etc/cron.d/cronjobs && crontab /etc/cron.d/cronjobs

# Créer un fichier de log pour cron
RUN touch /var/log/email-queue.log

# Entrypoint pour lancer cron au premier plan
CMD ["sh", "-c", "cron -f && tail -f /var/log/email-queue.log"]
