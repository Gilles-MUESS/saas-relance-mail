<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250604203144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence ADD user_email_account_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence ADD CONSTRAINT FK_5286D72B4ACDEFB8 FOREIGN KEY (user_email_account_id) REFERENCES user_email_account (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5286D72B4ACDEFB8 ON sequence (user_email_account_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence DROP FOREIGN KEY FK_5286D72B4ACDEFB8
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_5286D72B4ACDEFB8 ON sequence
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence DROP user_email_account_id
        SQL);
    }
}
