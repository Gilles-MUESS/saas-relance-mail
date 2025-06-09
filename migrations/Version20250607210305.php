<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250607210305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE queued_email (id INT AUTO_INCREMENT NOT NULL, message_id INT NOT NULL, account_id INT NOT NULL, scheduled_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', processing_started_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', sent_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', status VARCHAR(20) NOT NULL, retry_count INT DEFAULT 0 NOT NULL, error LONGTEXT DEFAULT NULL, INDEX IDX_FD2F4B89537A1329 (message_id), INDEX idx_queued_email_status_scheduled (status, scheduled_at), INDEX idx_queued_email_account (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE queued_email ADD CONSTRAINT FK_FD2F4B89537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE queued_email ADD CONSTRAINT FK_FD2F4B899B6B5FBA FOREIGN KEY (account_id) REFERENCES user_email_account (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE queued_email DROP FOREIGN KEY FK_FD2F4B89537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE queued_email DROP FOREIGN KEY FK_FD2F4B899B6B5FBA
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE queued_email
        SQL);
    }
}
