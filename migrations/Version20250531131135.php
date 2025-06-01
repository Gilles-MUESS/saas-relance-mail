<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250531131135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE user_email_account (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, provider VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, access_token VARCHAR(255) NOT NULL, refresh_token VARCHAR(255) DEFAULT NULL, access_token_expiry DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', provider_user_id VARCHAR(255) DEFAULT NULL, INDEX IDX_2E78933BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_email_account ADD CONSTRAINT FK_2E78933BA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_email_account DROP FOREIGN KEY FK_2E78933BA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_email_account
        SQL);
    }
}
